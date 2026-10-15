<?php
/**
 * Cierre de sesión. Solo por POST con token CSRF (el botón "Salir" del menú),
 * así un link malicioso no puede cerrarle la sesión a nadie.
 */
require_once __DIR__ . '/../includes/auth.php';

if (!es_post()) {
    $u = usuario_actual();
    redirigir($u ? dashboard_por_rol($u['rol']) : 'public/login.php');
}

csrf_verificar();
cerrar_sesion();
iniciar_sesion_segura();   // sesión nueva y limpia para poder mostrar el aviso
flash('info', 'Cerraste sesión correctamente.');
redirigir('public/login.php');
