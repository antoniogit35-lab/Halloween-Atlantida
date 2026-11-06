<?php
declare(strict_types=1);

require_once __DIR__ . '/usuarios.php';

/**
 * Escapa un texto para mostrarlo en HTML.
 */
function escapar(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige a otra página y detiene la ejecución.
 */
function redirigir(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Devuelve el token CSRF de la sesión, creándolo si hace falta.
 */
function tokenCsrf(): string
{
    iniciarSesion();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

/**
 * Campo oculto que cada formulario POST debe incluir.
 */
function campoCsrf(): string
{
    return '<input type="hidden" name="csrf" value="' . tokenCsrf() . '">';
}

/**
 * Comprueba que el token recibido coincide con el de la sesión.
 */
function csrfValido(): bool
{
    iniciarSesion();
    $enviado = $_POST['csrf'] ?? '';

    return is_string($enviado)
        && isset($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $enviado);
}

/**
 * Devuelve el valor enviado por POST ya escapado, para rellenar de nuevo el formulario.
 */
function valorPost(string $campo): string
{
    $valor = $_POST[$campo] ?? '';

    return is_string($valor) ? escapar($valor) : '';
}

/**
 * Devuelve el mensaje de error de un campo, o '' si no hay.
 */
function errorCampo(array $errores, string $campo): string
{
    return isset($errores[$campo])
        ? '<p class="error" role="alert">' . escapar($errores[$campo]) . '</p>'
        : '';
}