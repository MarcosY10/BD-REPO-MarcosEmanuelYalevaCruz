<?php
/** Pantalla de "no tenés permiso". La muestra requerir_rol(). */
require_once __DIR__ . '/header.php';
$u = usuario_actual();
?>
<div class="tarjeta tarjeta-centrada">
    <h1>Acceso denegado</h1>
    <p>No tenés permiso para ver esta página.</p>
    <a class="btn" href="<?= e(url($u ? dashboard_por_rol($u['rol']) : 'public/index.php')) ?>">Volver a mi panel</a>
</div>
<?php require __DIR__ . '/footer.php'; ?>
