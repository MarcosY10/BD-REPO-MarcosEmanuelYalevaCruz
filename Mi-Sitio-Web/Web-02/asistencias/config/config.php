<?php
/**
 * Configuración general del sistema.
 * Todo lo que se pueda querer ajustar después está acá, en un solo lugar.
 */

// --- Datos generales ---------------------------------------------------
define('APP_NOMBRE', 'Asistencias Escolares');
define('ANIO_LECTIVO', 2026);

// Carpeta del proyecto dentro de htdocs (http://localhost/asistencias).
// Si la carpeta se llama distinto, cambiá solo esto.
define('BASE_URL', '/asistencias');

// Dirección completa del sitio, SIN barra final. Se usa para armar los links
// que van por mail (recuperar contraseña). Si publicás el sistema, poné acá su dominio.
define('SITIO_URL', 'http://localhost');
define('MAIL_REMITENTE', 'no-responder@escuela.local');

// true mientras desarrollás (muestra errores y la página verificar.php).
// Poné false cuando el sistema se publique.
define('MODO_DESARROLLO', true);

// --- Sesión y login ----------------------------------------------------
define('SESION_INACTIVIDAD', 1800);   // segundos sin actividad antes de cerrar sesión (30 min)
define('MAX_INTENTOS_LOGIN', 5);      // intentos fallidos antes de bloquear
define('BLOQUEO_MINUTOS', 15);        // duración del bloqueo temporal
define('RESET_TOKEN_MINUTOS', 60);    // vencimiento del link de recuperar contraseña
define('CLAVE_MINIMA', 8);            // largo mínimo de las contraseñas nuevas
define('REGISTRO_MAX_INTENTOS', 5);   // intentos de clave institucional errónea antes de frenar el registro

// --- Cómputo de faltas -------------------------------------------------
// Cuánto suma cada estado. La justificada y los módulos libres no suman.
define('PESO_FALTA', [
    'ausente'  => 1.0,
    'tarde'    => 0.5,
    'retirado' => 0.5,
]);
define('ALERTA_FALTAS', 10);          // desde cuántas faltas se marca al alumno en los dashboards

// --- PHP ---------------------------------------------------------------
date_default_timezone_set('America/Argentina/Buenos_Aires');
mb_internal_encoding('UTF-8');

if (MODO_DESARROLLO) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
}
