<?php
/**
 * Sesión y control de acceso.
 *
 * Cómo se usa en cada página:
 *   require_once __DIR__ . '/../includes/auth.php';
 *   requerir_rol('admin');          // solo admin entra
 *   requerir_rol('admin', 'preceptora');   // cualquiera de los dos
 *   requerir_login();               // cualquier usuario con sesión
 *   requerir_invitado();            // solo para login/registro (si ya hay sesión, lo manda a su panel)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/funciones.php';

function iniciar_sesion_segura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,          // la cookie muere al cerrar el navegador
        'path'     => '/',
        'secure'   => $https,     // solo por HTTPS cuando el sitio lo use
        'httponly' => true,       // JavaScript no puede leer la cookie
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('ASISTENCIAS_SID');
    session_start();
}

iniciar_sesion_segura();

// --- Usuario en sesión -------------------------------------------------
function usuario_actual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function dashboard_por_rol(string $rol): string
{
    switch ($rol) {
        case 'admin':      return 'admin/dashboard.php';
        case 'preceptora': return 'preceptora/dashboard.php';
        default:           return 'alumno/dashboard.php';
    }
}

/** Guarda al usuario en la sesión después de validar sus credenciales. */
function iniciar_sesion_usuario(array $u): void
{
    session_regenerate_id(true);   // evita la fijación de sesión
    $_SESSION['usuario'] = [
        'id'                 => (int) $u['id'],
        'nombre'             => $u['nombre'],
        'apellido'           => $u['apellido'],
        'rol'                => $u['rol'],
        'debe_cambiar_clave' => (int) $u['debe_cambiar_clave'],
    ];
    $_SESSION['ultima_actividad'] = time();
}

function cerrar_sesion(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// --- Guardias de acceso ------------------------------------------------
function requerir_login(): array
{
    $u = usuario_actual();
    if ($u === null) {
        redirigir('public/login.php');
    }

    // Cierre por inactividad
    $ahora = time();
    if (isset($_SESSION['ultima_actividad']) && ($ahora - $_SESSION['ultima_actividad']) > SESION_INACTIVIDAD) {
        cerrar_sesion();
        iniciar_sesion_segura();
        flash('aviso', 'Tu sesión se cerró por inactividad. Ingresá de nuevo.');
        redirigir('public/login.php');
    }
    $_SESSION['ultima_actividad'] = $ahora;

    // Si el admin desactivó o bloqueó la cuenta, se corta en el momento
    $stmt = db()->prepare('SELECT estado, debe_cambiar_clave FROM usuarios WHERE id = ?');
    $stmt->execute([$u['id']]);
    $fila = $stmt->fetch();
    if (!$fila || $fila['estado'] !== 'activo') {
        cerrar_sesion();
        iniciar_sesion_segura();
        flash('error', 'Tu cuenta no está habilitada. Consultá con el administrador.');
        redirigir('public/login.php');
    }
    $_SESSION['usuario']['debe_cambiar_clave'] = (int) $fila['debe_cambiar_clave'];

    // Clave temporal: antes de usar el sistema tiene que cambiarla
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($fila['debe_cambiar_clave'] && $script !== 'cambiar_clave.php' && $script !== 'logout.php') {
        flash('aviso', 'Tenés que cambiar tu contraseña antes de continuar.');
        redirigir('comun/cambiar_clave.php');
    }

    return $_SESSION['usuario'];
}

function requerir_rol(string ...$roles): array
{
    $u = requerir_login();
    if (!in_array($u['rol'], $roles, true)) {
        http_response_code(403);
        $titulo = 'Acceso denegado';
        require __DIR__ . '/403.php';
        exit;
    }
    return $u;
}

function requerir_invitado(): void
{
    $u = usuario_actual();
    if ($u !== null) {
        redirigir(dashboard_por_rol($u['rol']));
    }
}
