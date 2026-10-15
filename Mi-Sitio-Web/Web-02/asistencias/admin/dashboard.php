<?php
/**
 * Panel del administrador: supervisión general.
 * (Etapa 3: totales y alumnos por curso. Asistencia de hoy y alertas llegan en la etapa 8.)
 */
require_once __DIR__ . '/../includes/auth.php';
$u = requerir_rol('admin');

$pdo = db();
$total_alumnos     = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'alumno' AND estado = 'activo'")->fetchColumn();
$total_preceptoras = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'preceptora' AND estado = 'activo'")->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT c.nombre,
            (SELECT COUNT(*) FROM alumno_curso ac
               JOIN usuarios a ON a.id = ac.alumno_id AND a.estado = \'activo\'
              WHERE ac.curso_id = c.id) AS alumnos
       FROM cursos c
      WHERE c.anio_lectivo = ?
      ORDER BY c.nombre'
);
$stmt->execute([ANIO_LECTIVO]);
$cursos = $stmt->fetchAll();

$titulo = 'Panel del administrador';
require __DIR__ . '/../includes/header.php';
?>
<div class="encabezado-pagina">
    <div>
        <h1>Hola, <?= e($u['nombre']) ?></h1>
        <p>Panel del administrador · año lectivo <?= (int) ANIO_LECTIVO ?></p>
    </div>
</div>

<div class="grilla grilla-3">
    <div class="stat stat-verde"><div class="stat-numero"><?= $total_alumnos ?></div><div class="stat-texto">Alumnos activos</div></div>
    <div class="stat"><div class="stat-numero"><?= $total_preceptoras ?></div><div class="stat-texto">Preceptoras activas</div></div>
    <div class="stat stat-naranja"><div class="stat-numero"><?= count($cursos) ?></div><div class="stat-texto">Cursos</div></div>
</div>

<div class="tarjeta mt">
    <h2>Alumnos por curso</h2>
    <div class="tabla-scroll">
        <table>
            <thead><tr><th>Curso</th><th class="centro">Alumnos</th></tr></thead>
            <tbody>
            <?php foreach ($cursos as $c): ?>
                <tr><td><?= e($c['nombre']) ?></td><td class="centro"><?= (int) $c['alumnos'] ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$cursos): ?>
                <tr><td colspan="2" class="texto-suave">Todavía no hay cursos cargados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="tarjeta">
    <h2>Próximamente</h2>
    <p class="mb0 texto-suave">Asistencia de hoy por curso, alumnos con muchas faltas, gestión de usuarios, horarios y reportes.</p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
