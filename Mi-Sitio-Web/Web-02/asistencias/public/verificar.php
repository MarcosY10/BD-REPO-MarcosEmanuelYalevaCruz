<?php
/**
 * Página de verificación del entorno (solo en modo desarrollo).
 * Abrir: http://localhost/asistencias/public/verificar.php
 * Se borra (o queda bloqueada con MODO_DESARROLLO = false) antes de publicar.
 */
require_once __DIR__ . '/../includes/auth.php';

if (!MODO_DESARROLLO) {
    http_response_code(404);
    exit('No encontrado.');
}

$verificaciones = [];
$agregar = function (string $nombre, bool $ok, string $detalle) use (&$verificaciones) {
    $verificaciones[] = ['nombre' => $nombre, 'ok' => $ok, 'detalle' => $detalle];
};

// PHP
$agregar('Versión de PHP (8.0 o más)', version_compare(PHP_VERSION, '8.0.0', '>='), PHP_VERSION);
$agregar('Extensión PDO MySQL', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'activa' : 'falta activarla en php.ini');
$agregar('Extensión mbstring', extension_loaded('mbstring'), extension_loaded('mbstring') ? 'activa' : 'falta activarla en php.ini');
$agregar('Sesión funcionando', session_status() === PHP_SESSION_ACTIVE, 'id: ' . substr(session_id(), 0, 6) . '…');

// Base de datos
$tablas_esperadas = [
    'usuarios', 'cursos', 'materias', 'horarios', 'alumno_curso', 'preceptora_curso',
    'asistencia_diaria', 'retiros', 'asistencia_clase', 'historial_cambios',
    'password_resets', 'normas', 'configuracion',
];
$conectado = false;
try {
    $pdo = db();
    $conectado = true;
    $agregar('Conexión a la base de datos', true, DB_NAME . ' en ' . DB_HOST . ':' . DB_PORT);

    $existentes = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $faltan = array_diff($tablas_esperadas, $existentes);
    $agregar('Tablas (' . count($tablas_esperadas) . ' esperadas)', !$faltan,
        $faltan ? 'faltan: ' . implode(', ', $faltan) : 'todas presentes');

    if (!$faltan) {
        $conteo = fn(string $sql) => (int) $pdo->query($sql)->fetchColumn();
        $agregar('Admin cargado', $conteo("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'") >= 1,
            $conteo("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'") . ' admin(s)');
        $agregar('Cursos y horarios', $conteo('SELECT COUNT(*) FROM cursos') > 0,
            $conteo('SELECT COUNT(*) FROM cursos') . ' cursos, ' . $conteo('SELECT COUNT(*) FROM horarios') . ' bloques de horario');
        $agregar('Alumnos matriculados', true, $conteo('SELECT COUNT(*) FROM alumno_curso') . ' alumnos');
    }
} catch (Throwable $ex) {
    $agregar('Conexión a la base de datos', false, $ex->getMessage());
}

$todo_ok = !in_array(false, array_column($verificaciones, 'ok'), true);

$titulo = 'Verificación del sistema';
require __DIR__ . '/../includes/header.php';
?>
<div class="encabezado-pagina">
    <div>
        <h1>Verificación del sistema</h1>
        <p>Etapa 2: estructura, conexión y estilos.</p>
    </div>
</div>

<div class="alerta <?= $todo_ok ? 'alerta-exito' : 'alerta-error' ?>">
    <?= $todo_ok ? 'Todo está en orden. Podemos seguir con el login.' : 'Hay cosas por resolver. Mirá las filas en rojo.' ?>
</div>

<div class="tarjeta">
    <div class="tabla-scroll">
        <table>
            <thead><tr><th>Control</th><th>Estado</th><th>Detalle</th></tr></thead>
            <tbody>
            <?php foreach ($verificaciones as $v): ?>
                <tr>
                    <td><?= e($v['nombre']) ?></td>
                    <td><?= $v['ok'] ? '<span class="badge badge-presente">OK</span>' : '<span class="badge badge-ausente">Falla</span>' ?></td>
                    <td><?= e($v['detalle']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<h2>Muestra de estilos</h2>
<div class="grilla grilla-4">
    <div class="stat stat-verde"><div class="stat-numero">26</div><div class="stat-texto">Alumnos</div></div>
    <div class="stat"><div class="stat-numero">2</div><div class="stat-texto">Cursos</div></div>
    <div class="stat stat-naranja"><div class="stat-numero">3</div><div class="stat-texto">Con muchas faltas</div></div>
    <div class="stat stat-rojo"><div class="stat-numero">1</div><div class="stat-texto">Asistencia pendiente</div></div>
</div>

<div class="tarjeta mt">
    <h3>Estados de asistencia</h3>
    <div class="fila">
        <?= badge_estado('presente') ?> <?= badge_estado('ausente') ?> <?= badge_estado('tarde') ?>
        <?= badge_estado('justificada') ?> <?= badge_estado('retirado') ?>
    </div>
    <div class="fila mt">
        <a class="btn" href="#">Botón principal</a>
        <a class="btn btn-borde" href="#">Botón secundario</a>
        <a class="btn btn-peligro" href="#">Acción de cuidado</a>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
