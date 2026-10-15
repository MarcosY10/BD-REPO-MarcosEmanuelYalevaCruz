<?php
/**
 * Panel de la preceptora. Solo ve los cursos que le asignó el admin.
 * (Etapa 3: panel base. La toma de asistencia llega en la etapa 6.)
 */
require_once __DIR__ . '/../includes/auth.php';
$u = requerir_rol('preceptora');

$stmt = db()->prepare(
    'SELECT c.id, c.nombre,
            (SELECT COUNT(*) FROM alumno_curso ac
              WHERE ac.curso_id = c.id AND ac.anio_lectivo = ?) AS alumnos
       FROM preceptora_curso pc
       JOIN cursos c ON c.id = pc.curso_id
      WHERE pc.preceptora_id = ? AND c.anio_lectivo = ?
      ORDER BY c.nombre'
);
$stmt->execute([ANIO_LECTIVO, $u['id'], ANIO_LECTIVO]);
$cursos = $stmt->fetchAll();

$titulo = 'Panel de preceptora';
require __DIR__ . '/../includes/header.php';
?>
<div class="encabezado-pagina">
    <div>
        <h1>Hola, <?= e($u['nombre']) ?></h1>
        <p>Panel de preceptora</p>
    </div>
</div>

<h2>Mis cursos</h2>
<?php if (!$cursos): ?>
    <div class="alerta alerta-aviso">Todavía no tenés cursos asignados. Pedile al administrador que te los asigne.</div>
<?php else: ?>
    <div class="grilla grilla-3">
        <?php foreach ($cursos as $c): ?>
            <div class="stat">
                <div class="stat-numero"><?= e($c['nombre']) ?></div>
                <div class="stat-texto"><?= (int) $c['alumnos'] ?> alumnos</div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="tarjeta mt">
    <h2>Próximamente</h2>
    <p class="mb0 texto-suave">Acá vas a tomar la asistencia de ingreso, registrar retiros y ver el estado de cada día.</p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
