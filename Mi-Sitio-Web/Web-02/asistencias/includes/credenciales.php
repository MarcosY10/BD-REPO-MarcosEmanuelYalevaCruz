<?php
/**
 * Lógica de credenciales: validar el login y las reglas de las contraseñas.
 * Se usa desde login.php, registro.php y cambiar_clave.php.
 */
require_once __DIR__ . '/auth.php';

const MSG_CREDENCIALES = 'Email o contraseña incorrectos. Si te equivocás varias veces seguidas, la cuenta se bloquea unos minutos.';

/**
 * Valida email + contraseña.
 * Devuelve ['ok' => true, 'usuario' => fila] o ['ok' => false, 'mensaje' => texto].
 *
 * Detalles de seguridad:
 *  - El mensaje de error es siempre el mismo, exista o no el email.
 *  - Si el email no existe igual se hace un cálculo de hash, para que la
 *    respuesta tarde parecido y no se pueda adivinar qué emails existen.
 *  - Tras MAX_INTENTOS_LOGIN fallos la cuenta se bloquea BLOQUEO_MINUTOS minutos.
 */
function intentar_login(string $email, string $clave): array
{
    $pdo  = db();
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    // Cuenta inexistente: gastar el mismo tiempo y devolver el mismo mensaje
    if (!$u) {
        password_hash($clave, PASSWORD_BCRYPT);
        return ['ok' => false, 'mensaje' => MSG_CREDENCIALES];
    }

    // Cuenta bloqueada temporalmente: ni se prueba la contraseña
    if ($u['bloqueado_hasta'] !== null && strtotime($u['bloqueado_hasta']) > time()) {
        return ['ok' => false, 'mensaje' => MSG_CREDENCIALES];
    }

    // Contraseña incorrecta: contar el fallo
    if (!password_verify($clave, $u['password_hash'])) {
        $fallos = (int) $u['intentos_fallidos'] + 1;
        if ($fallos >= MAX_INTENTOS_LOGIN) {
            $hasta = date('Y-m-d H:i:s', time() + BLOQUEO_MINUTOS * 60);
            $pdo->prepare('UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = ? WHERE id = ?')
                ->execute([$hasta, $u['id']]);
        } else {
            $pdo->prepare('UPDATE usuarios SET intentos_fallidos = ? WHERE id = ?')
                ->execute([$fallos, $u['id']]);
        }
        return ['ok' => false, 'mensaje' => MSG_CREDENCIALES];
    }

    // Contraseña correcta, pero la cuenta fue desactivada o bloqueada por el admin
    if ($u['estado'] !== 'activo') {
        return ['ok' => false, 'mensaje' => 'Tu cuenta no está habilitada. Consultá con el administrador.'];
    }

    // Todo bien: limpiar contadores (y actualizar el hash si el algoritmo mejoró)
    $pdo->prepare('UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?')
        ->execute([$u['id']]);
    if (password_needs_rehash($u['password_hash'], PASSWORD_BCRYPT)) {
        $pdo->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($clave, PASSWORD_BCRYPT), $u['id']]);
    }

    return ['ok' => true, 'usuario' => $u];
}

/** Reglas para una contraseña nueva. Devuelve el texto del error, o null si está bien. */
function validar_clave_nueva(string $clave): ?string
{
    if (strlen($clave) < CLAVE_MINIMA) {
        return 'La contraseña debe tener al menos ' . CLAVE_MINIMA . ' caracteres.';
    }
    if (strlen($clave) > 72) {   // límite real de bcrypt
        return 'La contraseña no puede tener más de 72 caracteres.';
    }
    if (!preg_match('/[A-Za-z]/', $clave) || !preg_match('/\d/', $clave)) {
        return 'La contraseña debe combinar letras y números.';
    }
    return null;
}

/** Contraseña tal cual la escribió el usuario (las contraseñas NO se recortan). */
function post_clave(string $campo): string
{
    $v = $_POST[$campo] ?? '';
    return is_string($v) ? $v : '';
}
