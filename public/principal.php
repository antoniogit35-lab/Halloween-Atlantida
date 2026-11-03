<?php
// Página principal: cementerio con acceso a las secciones.
// Un acceso con 'url' => null se muestra desactivado hasta que exista la página.
$accesos = [
    [
        'clase'  => 'cluedo',
        'imagen' => 'assets/img/fondos/cluedo.png',
        'texto'  => 'Asesinato en la mansión del código',
        'url'    => null,
    ],
    [
        'clase'  => 'recetario',
        'imagen' => 'assets/img/fondos/recetario.png',
        'texto'  => '<recetas> malditas </recetas>',
        'url'    => 'recetario.php',
    ],
    [
        'clase'  => 'ranking',
        'imagen' => 'assets/img/fondos/ranking.png',
        'texto'  => 'Top 36: ORDER BY muertes DESC',
        'url'    => null,
    ],
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Enterrados en código</title>
    <link rel="stylesheet" href="assets/css/principal.css">
</head>
<body>
    <header class="cabecera">
        <div class="cabecera-logo" aria-hidden="true"></div>
        <h1>Enterrados en código</h1>
        <div class="cabecera-usuario">
            <span class="sesion" aria-disabled="true">Iniciar sesión</span>
        </div>
    </header>

        <main class="cementerio">
            <?php foreach ($accesos as $acceso): ?>
                <?php
                $texto = htmlspecialchars($acceso['texto'], ENT_QUOTES, 'UTF-8');
                $imagen = htmlspecialchars($acceso['imagen'], ENT_QUOTES, 'UTF-8');
                ?>
                <?php if ($acceso['url'] !== null): ?>
                    <a class="zona <?= $acceso['clase'] ?>"
                    href="<?= htmlspecialchars($acceso['url'], ENT_QUOTES, 'UTF-8') ?>">
                        <img src="<?= $imagen ?>" alt="" loading="lazy">
                        <span class="zona-texto"><?= $texto ?></span>
                    </a>
                <?php else: ?>
                    <div class="zona desactivada <?= $acceso['clase'] ?>" aria-disabled="true">
                        <img src="<?= $imagen ?>" alt="" loading="lazy">
                        <span class="zona-texto"><?= $texto ?></span>
                        <span class="pronto">Próximamente</span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </main>

    <footer class="pie">
        <span aria-disabled="true">Sobre nosotros</span>
        <span aria-disabled="true">Contacta con Jack</span>
        <div class="pie-logo" aria-hidden="true"></div>
    </footer>
</body>
</html>