<?php
/**
 * Panel del alumno. Solo ve sus propios datos.
 * (Etapa 3: panel base. El porcentaje y el historial llegan en la etapa 7.)
 */
require_once __DIR__ . '/../includes/auth.php';
$u = requerir_rol('alumno');

$stmt = db()->prepare(
    'SELECT c.nombre
       FROM alumno_curso ac
       JOIN cursos c ON c.id = ac.curso_id
      WHERE ac.alumno_id = ? AND ac.anio_lectivo = ?'
);
$stmt->execute([$u['id'], ANIO_LECTIVO]);
$curso = $stmt->fetchColumn();

$titulo = 'Mi panel';
require __DIR__ . '/../includes/header.php';
?>
<div class="encabezado-pagina">
    <div>
        <h1>Hola, <?= e($u['nombre']) ?></h1>
        <p>Panel del alumno</p>
    </div>
</div>

<div class="grilla grilla-3">
    <div class="stat">
        <div class="stat-numero"><?= $curso ? e($curso) : '—' ?></div>
        <div class="stat-texto">Tu curso en <?= (int) ANIO_LECTIVO ?></div>
    </div>
</div>

<?php if (!$curso): ?>
    <div class="alerta alerta-aviso mt">Todavía no tenés un curso asignado. Consultá con el administrador.</div>
<?php endif; ?>

<div class="tarjeta mt">
    <h2>Próximamente</h2>
    <p class="mb0 texto-suave">Acá vas a ver tu porcentaje de asistencia, tus faltas y el detalle de cada materia.</p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
