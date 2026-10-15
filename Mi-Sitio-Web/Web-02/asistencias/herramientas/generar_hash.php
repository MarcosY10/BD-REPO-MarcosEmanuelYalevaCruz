<?php
/**
 * Convierte una contraseña corta en el hash que se guarda en la base de datos.
 *
 * Uso (desde la consola de Windows):
 *   C:\xampp\php\php.exe generar_hash.php miClave
 *
 * Después copiás el resultado en la columna password_hash de la tabla usuarios.
 * Solo funciona por consola, no desde el navegador.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Esta herramienta solo se usa por consola.');
}

if ($argc < 2 || $argv[1] === '') {
    fwrite(STDERR, "Uso: php generar_hash.php <contraseña>\n");
    exit(1);
}

echo password_hash($argv[1], PASSWORD_BCRYPT), PHP_EOL;
