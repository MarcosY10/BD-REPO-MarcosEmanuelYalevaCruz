<?php
/**
 * Conexión a la base de datos con PDO.
 * Se usa así, desde cualquier archivo:   $stmt = db()->prepare('SELECT ...');
 */
require_once __DIR__ . '/config.php';

// Usuario de la app (lo crea schema.sql, con permisos limitados).
// Si cambiás la contraseña en MySQL, cambiala también acá.
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'asistencias');
define('DB_USER', 'asistencias_app');
define('DB_PASS', 'Asist_App_2026!');

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // los errores SQL lanzan excepciones
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,                    // consultas preparadas de verdad
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4, time_zone = '-03:00'",
            ]);
        } catch (PDOException $e) {
            error_log('Error de conexión a la BD: ' . $e->getMessage());
            http_response_code(500);
            exit(MODO_DESARROLLO
                ? 'No se pudo conectar a la base de datos: ' . htmlspecialchars($e->getMessage())
                : 'No se pudo conectar a la base de datos. Intentá más tarde.');
        }
    }

    return $pdo;
}
