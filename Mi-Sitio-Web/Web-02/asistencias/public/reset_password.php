<?php
/**
 * Recuperación de contraseña, paso 2: el usuario llega desde el link del mail,
 * elige la contraseña nueva y el token queda usado.
 */
require_once __DIR__ . '/../includes/credenciales.php';
require_once __DIR__ . '/../includes/recuperacion.php';

requerir_invitado();

// El token viaja en la URL: que no se filtre a otros sitios ni quede en caché
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');

$valor = es_post() ? ($_POST['token'] ?? '') : ($_GET['token'] ?? '');
$token = is_string($valor) ? trim($valor) : '';

$registro = buscar_token_valido($token);
$errores  = [];

if ($registro && es_post()) {
    csrf_verificar();

    $nueva  = post_clave('nueva');
    $nueva2 = post_clave('nueva2');

    if ($msg = validar_clave_nueva($nueva)) {
        $errores['nueva'] = $msg;
    } elseif ($nueva !== $nueva2) {
        $errores['nueva2'] = 'Las contraseñas no coinciden.';
    }

    // Recién cuando la contraseña está bien se "gasta" el token
    if (!$errores) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            // Marca el token como usado solo si sigue sin usar y sin vencer
            // (si dos personas lo envían a la vez, solo una pasa)
            $stmt = $pdo->prepare('UPDATE password_resets SET usado = 1 WHERE id = ? AND usado = 0 AND expira_en > ?');
            $stmt->execute([$registro['reset_id'], date('Y-m-d H:i:s')]);

            if ($stmt->rowCount() !== 1) {
                $pdo->rollBack();
                flash('error', 'El link ya no es válido. Pedí uno nuevo.');
                redirigir('public/recuperar.php');
            }

            // Nueva contraseña; además se levanta cualquier bloqueo por intentos fallidos
            $pdo->prepare(
                'UPDATE usuarios
                    SET password_hash = ?, debe_cambiar_clave = 0, intentos_fallidos = 0, bloqueado_hasta = NULL
                  WHERE id = ?'
            )->execute([password_hash($nueva, PASSWORD_BCRYPT), $registro['id']]);

            // Cualquier otro link pendiente de esa cuenta queda anulado
            $pdo->prepare('UPDATE password_resets SET usado = 1 WHERE usuario_id = ? AND usado = 0')
                ->execute([$registro['id']]);

            $pdo->commit();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }

        flash('exito', 'Tu contraseña se cambió correctamente. Ya podés ingresar.');
        redirigir('public/login.php');
    }
}

function err_campo(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? '<div class="mensaje-error">' . e($errores[$campo]) . '</div>' : '';
}
function cls_campo(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? 'campo campo-error' : 'campo';
}

$titulo = 'Nueva contraseña';
$layout = 'auth';
require __DIR__ . '/../includes/header.php';
?>
<div class="tarjeta">
<?php if (!$registro): ?>
    <h1>Link no válido</h1>
    <p>Este link venció, ya se usó o no es correcto. Pedí uno nuevo para cambiar tu contraseña.</p>
    <a class="btn btn-ancho" href="<?= e(url('public/recuperar.php')) ?>">Pedir un link nuevo</a>
<?php else: ?>
    <h1>Elegí tu nueva contraseña</h1>
    <p class="texto-suave">Cuenta: <strong><?= e($registro['email']) ?></strong></p>

    <form method="post" action="" novalidate>
        <?= csrf_campo() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <div class="<?= cls_campo($errores, 'nueva') ?>">
            <label for="nueva">Contraseña nueva</label>
            <input type="password" id="nueva" name="nueva" autocomplete="new-password" maxlength="72" required autofocus>
            <div class="ayuda">Mínimo <?= (int) CLAVE_MINIMA ?> caracteres, con letras y números.</div>
            <?= err_campo($errores, 'nueva') ?>
        </div>

        <div class="<?= cls_campo($errores, 'nueva2') ?>">
            <label for="nueva2">Repetir contraseña</label>
            <input type="password" id="nueva2" name="nueva2" autocomplete="new-password" maxlength="72" required>
            <?= err_campo($errores, 'nueva2') ?>
        </div>

        <button type="submit" class="btn btn-ancho">Guardar contraseña</button>
    </form>
<?php endif; ?>
    <p class="auth-pie mb0"><a href="<?= e(url('public/login.php')) ?>">Volver a ingresar</a></p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
