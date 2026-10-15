<?php
/**
 * Recuperación de contraseña: creación y validación del token, y envío del link.
 *
 * Cómo funciona:
 *  1. Se genera un token aleatorio de 64 caracteres y se manda por mail dentro de un link.
 *  2. En la base se guarda SOLO su hash (SHA-256): si alguien viera la tabla, no podría usar los tokens.
 *  3. El token vence a la hora (RESET_TOKEN_MINUTOS) y sirve una sola vez.
 */
require_once __DIR__ . '/auth.php';

const RECUPERACION_MAX_POR_HORA = 3;   // pedidos por cuenta por hora (frena el spam de mails)

/**
 * Crea un token para el usuario. Devuelve el token (para armar el link)
 * o null si ya pidió demasiados en la última hora.
 */
function crear_token_recuperacion(int $usuario_id): ?string
{
    $pdo   = db();
    $ahora = time();

    // Limpieza: borrar tokens vencidos hace más de un día
    $pdo->prepare('DELETE FROM password_resets WHERE expira_en < ?')
        ->execute([date('Y-m-d H:i:s', $ahora - 86400)]);

    // Límite de pedidos por hora
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM password_resets WHERE usuario_id = ? AND creado_en > ?');
    $stmt->execute([$usuario_id, date('Y-m-d H:i:s', $ahora - 3600)]);
    if ((int) $stmt->fetchColumn() >= RECUPERACION_MAX_POR_HORA) {
        return null;
    }

    // Un pedido nuevo anula los anteriores sin usar
    $pdo->prepare('UPDATE password_resets SET usado = 1 WHERE usuario_id = ? AND usado = 0')
        ->execute([$usuario_id]);

    $token = bin2hex(random_bytes(32));
    $pdo->prepare('INSERT INTO password_resets (usuario_id, token_hash, expira_en, usado, creado_en) VALUES (?, ?, ?, 0, ?)')
        ->execute([
            $usuario_id,
            hash('sha256', $token),
            date('Y-m-d H:i:s', $ahora + RESET_TOKEN_MINUTOS * 60),
            date('Y-m-d H:i:s', $ahora),
        ]);

    return $token;
}

/**
 * Busca un token que todavía sirva (existe, sin usar, sin vencer y de una cuenta activa).
 * Devuelve ['reset_id', 'id', 'nombre', 'email'] o null.
 */
function buscar_token_valido(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }
    $stmt = db()->prepare(
        "SELECT pr.id AS reset_id, u.id, u.nombre, u.email
           FROM password_resets pr
           JOIN usuarios u ON u.id = pr.usuario_id
          WHERE pr.token_hash = ? AND pr.usado = 0 AND pr.expira_en > ? AND u.estado = 'activo'"
    );
    $stmt->execute([hash('sha256', $token), date('Y-m-d H:i:s')]);
    return $stmt->fetch() ?: null;
}

/**
 * Manda el mail con el link de recuperación.
 *  - Modo desarrollo: NO manda nada; deja el mail en logs/correos.log
 *    (y la página muestra el link en pantalla).
 *  - Modo producción: usa mail() de PHP. Para algo más confiable conviene
 *    cambiar esta función por PHPMailer con un SMTP de la escuela.
 */
function enviar_link_recuperacion(array $usuario, string $link): void
{
    $asunto = 'Recuperar tu contraseña - ' . APP_NOMBRE;
    $cuerpo = "Hola " . $usuario['nombre'] . ",\n\n"
            . "Recibimos un pedido para cambiar tu contraseña. Entrá a este link:\n\n"
            . $link . "\n\n"
            . "El link sirve una sola vez y vence en " . RESET_TOKEN_MINUTOS . " minutos.\n"
            . "Si no lo pediste vos, ignorá este mensaje: tu contraseña no cambia.\n";

    if (MODO_DESARROLLO) {
        $linea = '[' . date('Y-m-d H:i:s') . "] Para: {$usuario['email']}\nAsunto: $asunto\n$cuerpo\n---\n";
        @file_put_contents(__DIR__ . '/../logs/correos.log', $linea, FILE_APPEND | LOCK_EX);
        return;
    }

    $cabeceras = "From: " . MAIL_REMITENTE . "\r\n"
               . "Content-Type: text/plain; charset=UTF-8\r\n";
    $asunto_codificado = mb_encode_mimeheader($asunto, 'UTF-8');
    if (!@mail($usuario['email'], $asunto_codificado, $cuerpo, $cabeceras)) {
        error_log('No se pudo enviar el mail de recuperación a ' . $usuario['email']);
    }
}
