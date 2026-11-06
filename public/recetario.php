<?php

require_once __DIR__ . '/../app/recetas.php';

$errorRecetario = null;
$recetasJSON = '[]';

try {
    $recetas = cargarRecetasCSV(
        __DIR__ . '/../recursos/datos/recetas.csv'
    );

    $recetasJSON = json_encode(
        $recetas,
        JSON_UNESCAPED_UNICODE
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
        | JSON_THROW_ON_ERROR
    );
} catch (Throwable $error) {
    error_log('Error del recetario: ' . $error->getMessage());
    $errorRecetario = 'No se ha podido cargar el recetario. Inténtalo más tarde.';
}

?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Recetas malditas</title><link rel="stylesheet" href="assets/css/recetario.css"></head>
<body>
    <?php if ($errorRecetario !== null): ?>
        <p role="alert">
            <?= htmlspecialchars($errorRecetario, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>
    <main class="catalogo">
<header class="cabecera">
    <a class="cabecera__volver" href="principal.php">
        <span aria-hidden="true">←</span>
        Volver a la página principal
    </a>

    <h1><span>&lt;recetas&gt;</span> malditas <span>&lt;/recetas&gt;</span></h1>

    <p id="contador" role="status" aria-live="polite">Cargando recetario…</p>
</header>
<section class="herramientas" aria-label="Búsqueda"><h2>El recetario</h2><label class="busqueda"><span class="sr-only">Buscar receta por nombre</span><input id="buscador" type="search" placeholder="Buscar receta…" autocomplete="off"><span aria-hidden="true">⌕</span></label></section>
<section id="grid" class="grid" aria-label="Recetas"></section><p id="vacio" hidden>No hay recetas con ese nombre.</p>
</main>
<dialog id="ficha" aria-labelledby="nombre-modal"><div class="ficha-interior"><button id="cerrar" class="cerrar" aria-label="Cerrar receta">×</button><div class="resumen"><div id="foto-modal"></div><div class="datos"><h2 id="nombre-modal"></h2><dl id="datos-modal"></dl></div></div><section class="seccion"><h3>Ingredientes</h3><ul id="ingredientes"></ul></section><section class="seccion"><h3>Preparación</h3><ol id="preparacion"></ol></section></div></dialog>
<script id="datos-iniciales" type="application/json">
<?= $recetasJSON ?>
</script><script src="assets/js/recetario.js"></script></body></html>