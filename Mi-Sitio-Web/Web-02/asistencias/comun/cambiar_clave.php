<?php
/**
 * Cambio de contraseña. Lo usan los tres roles.
 * Es obligatorio en el primer ingreso cuando la cuenta tiene clave temporal
 * (por ejemplo el admin que viene de la base de datos).
 */
require_once __DIR__ . '/../includes/credenciales.php';

$u = requerir_login();   // cualquier rol, con sesión

$obligatorio = !empty($u['debe_cambiar_clave']);
$errores = [];

if (es_post()) {
    csrf_verificar();

    $actual = post_clave('actual');
    $nueva  = post_clave('nueva');
    $nueva2 = post_clave('nueva2');

    $stmt = db()->prepare('SELECT password_hash FROM usuarios WHERE id = ?');
    $stmt->execute([$u['id']]);
    $hash = (string) $stmt->fetchColumn();

    if (!password_verify($actual, $hash)) {
        $errores['actual'] = 'La contraseña actual no es correcta.';
    }
    if ($msg = validar_clave_nueva($nueva)) {
        $errores['nueva'] = $msg;
    } elseif ($nueva === $actual) {
        $errores['nueva'] = 'La contraseña nueva tiene que ser distinta de la actual.';
    } elseif ($nueva !== $nueva2) {
        $errores['nueva2'] = 'Las contraseñas no coinciden.';
    }

    if (!$errores) {
        db()->prepare('UPDATE usuarios SET password_hash = ?, debe_cambiar_clave = 0 WHERE id = ?')
            ->execute([password_hash($nueva, PASSWORD_BCRYPT), $u['id']]);

        session_regenerate_id(true);
        $_SESSION['usuario']['debe_cambiar_clave'] = 0;

        flash('exito', 'Tu contraseña se cambió correctamente.');
        redirigir(dashboard_por_rol($u['rol']));
    }
}

function err(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? '<div class="mensaje-error">' . e($errores[$campo]) . '</div>' : '';
}
function cls(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? 'campo campo-error' : 'campo';
}

$titulo = 'Cambiar contraseña';
require __DIR__ . '/../includes/header.php';
?>
<div class="tarjeta" style="max-width:520px;margin:1rem auto;">
    <h1>Cambiar contraseña</h1>

    <?php if ($obligatorio): ?>
        <div class="alerta alerta-aviso">Tu cuenta tiene una contraseña temporal. Elegí una propia para continuar.</div>
    <?php endif; ?>

    <form method="post" action="" novalidate>
        <?= csrf_campo() ?>

        <div class="<?= cls($errores, 'actual') ?>">
            <label for="actual">Contraseña actual</label>
            <input type="password" id="actual" name="actual" autocomplete="current-password" maxlength="72" required autofocus>
            <?= err($errores, 'actual') ?>
        </div>

        <div class="<?= cls($errores, 'nueva') ?>">
            <label for="nueva">Contraseña nueva</label>
            <input type="password" id="nueva" name="nueva" autocomplete="new-password" maxlength="72" required>
            <div class="ayuda">Mínimo <?= (int) CLAVE_MINIMA ?> caracteres, con letras y números.</div>
            <?= err($errores, 'nueva') ?>
        </div>

        <div class="<?= cls($errores, 'nueva2') ?>">
            <label for="nueva2">Repetir contraseña nueva</label>
            <input type="password" id="nueva2" name="nueva2" autocomplete="new-password" maxlength="72" required>
            <?= err($errores, 'nueva2') ?>
        </div>

        <button type="submit" class="btn">Guardar contraseña</button>
        <?php if (!$obligatorio): ?>
            <a class="btn btn-borde" href="<?= e(url(dashboard_por_rol($u['rol']))) ?>">Cancelar</a>
        <?php endif; ?>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
