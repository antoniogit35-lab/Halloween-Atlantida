<?php
declare(strict_types=1);
require __DIR__ . '/../app/seguridad.php';

// Solo se cierra la sesión con un envío POST válido (con token CSRF).
// Así, otra web no puede cerrarla con un simple enlace.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrfValido()) {
    cerrarSesion();
}

redirigir('principal.php');