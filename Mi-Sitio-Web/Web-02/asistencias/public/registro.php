<?php
/**
 * Registro de PRECEPTORAS (los alumnos los crea el administrador).
 * Para registrarse hace falta la clave institucional, que guarda el admin
 * en la tabla `configuracion`. La preceptora entra directo, sin aprobación.
 */
require_once __DIR__ . '/../includes/credenciales.php';

requerir_invitado();

$errores = [];
$datos   = ['nombre' => '', 'apellido' => '', 'dni' => '', 'email' => ''];

// Freno contra adivinar la clave institucional (por sesión)
$bloqueado_hasta = (int) ($_SESSION['reg_bloqueo_hasta'] ?? 0);
$frenado = $bloqueado_hasta > time();

if (es_post()) {
    csrf_verificar();

    foreach ($datos as $campo => $_) {
        $datos[$campo] = post_texto($campo);
    }
    $datos['email'] = mb_strtolower($datos['email']);
    $datos['dni']   = preg_replace('/[.\s]/', '', $datos['dni']);
    $clave          = post_clave('password');
    $clave2         = post_clave('password2');
    $codigo         = post_texto('codigo');

    if ($frenado) {
        $errores['codigo'] = 'Demasiados intentos con la clave institucional. Probá más tarde.';
    } else {
        // --- Validaciones de formato ---
        $patron_nombre = '/^[\p{L}\p{M}\' .-]{2,80}$/u';
        if (!preg_match($patron_nombre, $datos['nombre'])) {
            $errores['nombre'] = 'Ingresá tu nombre (solo letras, entre 2 y 80 caracteres).';
        }
        if (!preg_match($patron_nombre, $datos['apellido'])) {
            $errores['apellido'] = 'Ingresá tu apellido (solo letras, entre 2 y 80 caracteres).';
        }
        if (!preg_match('/^\d{7,9}$/', $datos['dni'])) {
            $errores['dni'] = 'El DNI debe tener entre 7 y 9 números.';
        }
        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($datos['email']) > 150) {
            $errores['email'] = 'Ingresá un email válido.';
        }
        if ($msg = validar_clave_nueva($clave)) {
            $errores['password'] = $msg;
        } elseif ($clave !== $clave2) {
            $errores['password2'] = 'Las contraseñas no coinciden.';
        }

        // --- Clave institucional ---
        if ($codigo === '') {
            $errores['codigo'] = 'Ingresá la clave institucional.';
        } else {
            $stmt = db()->prepare("SELECT valor FROM configuracion WHERE clave = 'clave_registro_preceptoras'");
            $stmt->execute();
            $correcta = (string) $stmt->fetchColumn();

            if ($correcta === '' || !hash_equals($correcta, $codigo)) {
                $errores['codigo'] = 'La clave institucional no es correcta.';
                $_SESSION['reg_fallos'] = (int) ($_SESSION['reg_fallos'] ?? 0) + 1;
                if ($_SESSION['reg_fallos'] >= REGISTRO_MAX_INTENTOS) {
                    $_SESSION['reg_bloqueo_hasta'] = time() + BLOQUEO_MINUTOS * 60;
                    $_SESSION['reg_fallos'] = 0;
                }
            }
        }

        // --- Crear la cuenta ---
        if (!$errores) {
            try {
                $stmt = db()->prepare(
                    "INSERT INTO usuarios (nombre, apellido, dni, email, password_hash, rol, estado, debe_cambiar_clave)
                     VALUES (?, ?, ?, ?, ?, 'preceptora', 'activo', 0)"
                );
                $stmt->execute([
                    $datos['nombre'], $datos['apellido'], $datos['dni'], $datos['email'],
                    password_hash($clave, PASSWORD_BCRYPT),
                ]);
                $nuevo_id = (int) db()->lastInsertId();

                unset($_SESSION['reg_fallos'], $_SESSION['reg_bloqueo_hasta']);

                $fila = db()->prepare('SELECT * FROM usuarios WHERE id = ?');
                $fila->execute([$nuevo_id]);
                iniciar_sesion_usuario($fila->fetch());

                flash('exito', 'Tu cuenta fue creada. El administrador debe asignarte tus cursos para que puedas tomar asistencia.');
                redirigir('preceptora/dashboard.php');
            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') {   // email o DNI repetido
                    $errores['email'] = 'Ya existe una cuenta con ese email o ese DNI.';
                } else {
                    throw $ex;
                }
            }
        }
    }
}

/** Atajo para mostrar el error de un campo */
function error_campo(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? '<div class="mensaje-error">' . e($errores[$campo]) . '</div>' : '';
}
function clase_campo(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? 'campo campo-error' : 'campo';
}

$titulo = 'Registro de preceptoras';
$layout = 'auth';
require __DIR__ . '/../includes/header.php';
?>
<div class="tarjeta">
    <h1>Registro de preceptoras</h1>
    <p class="texto-suave">Necesitás la clave institucional que te dio la escuela.</p>

    <form method="post" action="" novalidate>
        <?= csrf_campo() ?>

        <div class="campos-doble">
            <div class="<?= clase_campo($errores, 'nombre') ?>">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" maxlength="80" required autofocus>
                <?= error_campo($errores, 'nombre') ?>
            </div>
            <div class="<?= clase_campo($errores, 'apellido') ?>">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" value="<?= e($datos['apellido']) ?>" maxlength="80" required>
                <?= error_campo($errores, 'apellido') ?>
            </div>
        </div>

        <div class="<?= clase_campo($errores, 'dni') ?>">
            <label for="dni">DNI</label>
            <input type="text" id="dni" name="dni" value="<?= e($datos['dni']) ?>" inputmode="numeric" maxlength="12" required>
            <?= error_campo($errores, 'dni') ?>
        </div>

        <div class="<?= clase_campo($errores, 'email') ?>">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($datos['email']) ?>" autocomplete="username" maxlength="150" required>
            <?= error_campo($errores, 'email') ?>
        </div>

        <div class="<?= clase_campo($errores, 'password') ?>">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" autocomplete="new-password" maxlength="72" required>
            <div class="ayuda">Mínimo <?= (int) CLAVE_MINIMA ?> caracteres, con letras y números.</div>
            <?= error_campo($errores, 'password') ?>
        </div>

        <div class="<?= clase_campo($errores, 'password2') ?>">
            <label for="password2">Repetir contraseña</label>
            <input type="password" id="password2" name="password2" autocomplete="new-password" maxlength="72" required>
            <?= error_campo($errores, 'password2') ?>
        </div>

        <div class="<?= clase_campo($errores, 'codigo') ?>">
            <label for="codigo">Clave institucional</label>
            <input type="password" id="codigo" name="codigo" autocomplete="off" maxlength="100" required>
            <?= error_campo($errores, 'codigo') ?>
        </div>

        <button type="submit" class="btn btn-ancho">Crear cuenta</button>
    </form>

    <p class="auth-pie mb0">¿Ya tenés cuenta? <a href="<?= e(url('public/login.php')) ?>">Ingresar</a></p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
