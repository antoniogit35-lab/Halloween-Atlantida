<?php
// Página de error 404 personalizada. También devuelve el código 404 si se abre directamente.
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Te has perdido · Enterrados en código</title>
    <!-- Rutas desde la raíz: la página puede mostrarse en cualquier dirección que no exista -->
     <link rel="stylesheet" href="/assets/css/fuentes.css">
    <link rel="stylesheet" href="/assets/css/error404.css">
</head>
<body>
    <main class="escenario">
        <picture>
            <source media="(max-aspect-ratio: 1/1)" srcset="/assets/img/fondos/404-movil.jpg">
            <img
                class="escenario-imagen"
                src="/assets/img/fondos/404-escritorio.jpg"
                alt=""
                width="1672"
                height="941"
                fetchpriority="high"
            >
        </picture>

        <section class="mensaje-fantasma">
            <p class="codigo-error">404</p>
            <h1>Parece que te has perdido en este laberíntico cementerio.</h1>
            <p class="pregunta">¿Quieres que te guíe a la entrada?</p>
            <a class="boton-guia" href="/principal.php">Guíame a la entrada</a>
        </section>
    </main>
</body>
</html>