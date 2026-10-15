<?php
/**
 * Inicio de sesión (validación de usuario).
 */
require_once __DIR__ . '/../includes/credenciales.php';

requerir_invitado();   // si ya hay sesión, lo manda a su panel

$error = '';
$email = '';

if (es_post()) {
    csrf_verificar();

    $email = mb_strtolower(post_texto('email'));
    $clave = post_clave('password');

    if ($email === '' || $clave === '') {
        $error = 'Ingresá tu email y tu contraseña.';
    } else {
        $resultado = intentar_login($email, $clave);
        if ($resultado['ok']) {
            iniciar_sesion_usuario($resultado['usuario']);
            redirigir(dashboard_por_rol($resultado['usuario']['rol']));
        }
        $error = $resultado['mensaje'];
    }
}

$titulo = 'Ingresar';
$layout = 'auth';
require __DIR__ . '/../includes/header.php';
?>
<div class="tarjeta">
    <h1>Ingresar</h1>
    <p class="texto-suave">Usá el email y la contraseña de tu cuenta.</p>

    <?php if ($error): ?>
        <div class="alerta alerta-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="" novalidate>
        <?= csrf_campo() ?>

        <div class="campo">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>"
                   autocomplete="username" maxlength="150" required autofocus>
        </div>

        <div class="campo">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password"
                   autocomplete="current-password" maxlength="72" required>
        </div>

        <button type="submit" class="btn btn-ancho">Ingresar</button>
    </form>

    <p class="auth-pie mb0">
        <?php if (is_file(__DIR__ . '/recuperar.php')): ?>
            <a href="<?= e(url('public/recuperar.php')) ?>">¿Olvidaste tu contraseña?</a><br>
        <?php endif; ?>
        ¿Sos preceptora y todavía no tenés cuenta? <a href="<?= e(url('public/registro.php')) ?>">Registrate</a>
    </p>
</div>
<p class="texto-centro texto-suave"><small>Los alumnos reciben su cuenta del administrador de la escuela.</small></p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
