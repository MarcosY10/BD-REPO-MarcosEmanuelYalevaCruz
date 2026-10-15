<?php
/**
 * Recuperación de contraseña, paso 1: el usuario pide el link con su email.
 *
 * Por seguridad el mensaje es SIEMPRE el mismo, exista o no el email,
 * así nadie puede averiguar qué correos están registrados.
 */
require_once __DIR__ . '/../includes/recuperacion.php';

requerir_invitado();

$error = '';
$email = '';

if (es_post()) {
    csrf_verificar();
    $email = mb_strtolower(post_texto('email'));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresá un email válido.';
    } else {
        $stmt = db()->prepare("SELECT id, nombre, email FROM usuarios WHERE email = ? AND estado = 'activo'");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $token = crear_token_recuperacion((int) $usuario['id']);
            if ($token !== null) {
                $link = SITIO_URL . url('public/reset_password.php?token=' . $token);
                enviar_link_recuperacion($usuario, $link);
                if (MODO_DESARROLLO) {
                    $_SESSION['dev_link'] = $link;   // solo para mostrarlo en pantalla en desarrollo
                }
            }
        }

        flash('info', 'Si ese email está registrado, te enviamos un link para cambiar la contraseña. Revisá tu correo; el link vence en ' . RESET_TOKEN_MINUTOS . ' minutos.');
        redirigir('public/recuperar.php');
    }
}

// Link de desarrollo (se muestra una sola vez)
$dev_link = $_SESSION['dev_link'] ?? null;
unset($_SESSION['dev_link']);

$titulo = 'Recuperar contraseña';
$layout = 'auth';
require __DIR__ . '/../includes/header.php';
?>
<div class="tarjeta">
    <h1>Recuperar contraseña</h1>
    <p class="texto-suave">Ingresá el email de tu cuenta y te enviamos un link para elegir una contraseña nueva.</p>

    <?php if ($error): ?>
        <div class="alerta alerta-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($dev_link): ?>
        <div class="alerta alerta-aviso">
            <strong>Modo desarrollo:</strong> no se envían mails, el link queda en <code>logs/correos.log</code> y acá:<br>
            <a href="<?= e($dev_link) ?>" style="word-break:break-all;"><?= e($dev_link) ?></a>
        </div>
    <?php endif; ?>

    <form method="post" action="" novalidate>
        <?= csrf_campo() ?>

        <div class="campo">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>"
                   autocomplete="username" maxlength="150" required autofocus>
        </div>

        <button type="submit" class="btn btn-ancho">Enviar link</button>
    </form>

    <p class="auth-pie mb0"><a href="<?= e(url('public/login.php')) ?>">Volver a ingresar</a></p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
