<?php
/**
 * Funciones de ayuda que se usan en todo el sistema.
 */
require_once __DIR__ . '/../config/config.php';

// --- Salida segura (contra XSS) ----------------------------------------
/** Escapa un texto antes de mostrarlo en HTML. Usarlo SIEMPRE: <?= e($dato) ?> */
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// --- URLs y redirecciones ----------------------------------------------
/** Arma una URL del sitio: url('public/login.php') => /asistencias/public/login.php */
function url(string $ruta = ''): string
{
    return BASE_URL . '/' . ltrim($ruta, '/');
}

function redirigir(string $ruta): void
{
    header('Location: ' . url($ruta));
    exit;
}

// --- Mensajes que se muestran una sola vez (flash) ----------------------
/** $tipo: exito | error | info | aviso */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function obtener_flashes(): array
{
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensajes;
}

// --- CSRF: protege los formularios que modifican datos ------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Campo oculto para poner dentro de cada <form method="post"> */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Llamar al principio de todo código que procese un POST. */
function csrf_verificar(): void
{
    $enviado = $_POST['csrf'] ?? '';
    if (!is_string($enviado) || !hash_equals(csrf_token(), $enviado)) {
        http_response_code(419);
        exit('La sesión del formulario venció. Volvé atrás, recargá la página y probá de nuevo.');
    }
}

// --- Datos de formularios -----------------------------------------------
/** Texto de un campo POST, sin espacios sobrantes. */
function post_texto(string $campo): string
{
    $valor = $_POST[$campo] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

function es_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

// --- Fechas y textos -----------------------------------------------------
function formatear_fecha(?string $fecha): string
{
    if (!$fecha) {
        return '';
    }
    $f = DateTime::createFromFormat('Y-m-d', substr($fecha, 0, 10));
    return $f ? $f->format('d/m/Y') : '';
}

function formatear_hora(?string $hora): string
{
    return $hora ? substr($hora, 0, 5) : '';
}

/** 1 = Lunes ... 5 = Viernes */
function nombre_dia(int $numero): string
{
    $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    return $dias[$numero] ?? '';
}

/** Etiqueta de color para un estado de asistencia. */
function badge_estado(string $estado): string
{
    $nombres = [
        'presente'     => 'Presente',
        'ausente'      => 'Ausente',
        'tarde'        => 'Tarde',
        'justificada'  => 'Justificada',
        'retirado'     => 'Retirado',
    ];
    $texto = $nombres[$estado] ?? $estado;
    return '<span class="badge badge-' . e($estado) . '">' . e($texto) . '</span>';
}
