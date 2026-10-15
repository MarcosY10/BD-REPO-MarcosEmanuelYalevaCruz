<?php
/**
 * Punto de entrada: según haya sesión o no, manda al panel o al login.
 */
require_once __DIR__ . '/../includes/auth.php';

$u = usuario_actual();
if ($u !== null) {
    redirigir(dashboard_por_rol($u['rol']));
}

redirigir('public/login.php');
