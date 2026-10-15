<?php
/**
 * Cabecera común. Antes de incluirla, la página define:
 *   $titulo  (texto del título, obligatorio)
 *   $layout  ('app' por defecto, o 'auth' para login/registro: sin menú y centrado)
 * Siempre se incluye DESPUÉS de auth.php.
 */
$titulo = $titulo ?? APP_NOMBRE;
$layout = $layout ?? 'app';
$u      = usuario_actual();
$actual = basename($_SERVER['SCRIPT_NAME'] ?? '');

// Menú de cada rol: [archivo, texto del link]
$menus = [
    'alumno' => [
        ['alumno/dashboard.php',        'Inicio'],
        ['alumno/mis_asistencias.php',  'Mis asistencias'],
        ['alumno/mi_horario.php',       'Mi horario'],
        ['comun/normas.php',            'Normas'],
    ],
    'preceptora' => [
        ['preceptora/dashboard.php',        'Inicio'],
        ['preceptora/tomar_asistencia.php', 'Tomar asistencia'],
        ['preceptora/ver_curso.php',        'Mis cursos'],
        ['preceptora/historial.php',        'Historial'],
        ['comun/normas.php',                'Normas'],
    ],
    'admin' => [
        ['admin/dashboard.php',          'Inicio'],
        ['admin/alumnos.php',            'Alumnos'],
        ['admin/preceptoras.php',        'Preceptoras'],
        ['admin/cursos.php',             'Cursos'],
        ['admin/materias_horarios.php',  'Horarios'],
        ['admin/reportes.php',           'Reportes'],
        ['admin/normas.php',             'Normas'],
        ['admin/configuracion.php',      'Configuración'],
    ],
];
$etiquetas_rol = ['alumno' => 'Alumno', 'preceptora' => 'Preceptora', 'admin' => 'Administrador'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · <?= e(APP_NOMBRE) ?></title>
    <link rel="stylesheet" href="<?= e(url('public/css/estilos.css')) ?>">
</head>
<body class="layout-<?= e($layout) ?>">

<?php if ($layout === 'app'): ?>
<header class="topbar">
    <div class="topbar-interior">
        <a class="marca" href="<?= e(url($u ? dashboard_por_rol($u['rol']) : 'public/index.php')) ?>">
            <span class="marca-icono" aria-hidden="true">✔</span> <?= e(APP_NOMBRE) ?>
        </a>

        <?php if ($u): ?>
        <input type="checkbox" id="menu-toggle" class="menu-toggle">
        <label for="menu-toggle" class="menu-boton" aria-label="Abrir menú">☰</label>

        <nav class="menu">
            <?php foreach ($menus[$u['rol']] ?? [] as [$archivo, $texto]): ?>
                <?php if (!is_file(__DIR__ . '/../' . $archivo)) { continue; } // links solo a páginas ya construidas ?>
                <a href="<?= e(url($archivo)) ?>" class="<?= basename($archivo) === $actual ? 'activo' : '' ?>"><?= e($texto) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="usuario">
            <span class="usuario-nombre">
                <?= e($u['nombre'] . ' ' . $u['apellido']) ?>
                <small><?= e($etiquetas_rol[$u['rol']] ?? '') ?></small>
            </span>
            <a href="<?= e(url('comun/cambiar_clave.php')) ?>" class="link-suave">Contraseña</a>
            <form method="post" action="<?= e(url('public/logout.php')) ?>" class="form-inline">
                <?= csrf_campo() ?>
                <button type="submit" class="btn btn-borde btn-chico">Salir</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</header>
<main class="contenedor">
<?php else: ?>
<main class="auth-caja">
    <div class="auth-marca"><span class="marca-icono" aria-hidden="true">✔</span> <?= e(APP_NOMBRE) ?></div>
<?php endif; ?>

<?php foreach (obtener_flashes() as $f): ?>
    <div class="alerta alerta-<?= e($f['tipo']) ?>" role="alert"><?= e($f['mensaje']) ?></div>
<?php endforeach; ?>
