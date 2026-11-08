<?php
declare(strict_types=1);
require __DIR__ . '/../app/usuarios.php';
require __DIR__ . '/../app/seguridad.php';
$sesion = usuarioActual();
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enterrados en código</title>
    <link rel="stylesheet" href="assets/css/principal.css">
</head>
<body data-asesino="<?= $sesion !== null ? htmlspecialchars((string) $sesion['asesino'], ENT_QUOTES, 'UTF-8') : '' ?>">
    <header class="cabecera">
        <div class="reserva-logo" aria-hidden="true"></div>

        <h1>Enterrados en código</h1>

        <div class="cabecera-acceso">
            <?php if ($sesion !== null): ?>
                <div class="sesion-activa">
                    <p class="sesion-saludo">
                        <span class="sesion-saludo__texto">Disfruta del cementerio, </span>
                        <strong><?= htmlspecialchars($sesion['usuario'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </p>
                    <button type="button" class="sesion-salir" id="abrir-salir"
                            aria-haspopup="dialog" aria-controls="confirmar-salir">
                        Salir del cementerio
                    </button>
                </div>
            <?php else: ?>
                <a class="sesion-enlace" href="login.php">Iniciar sesión</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="principal">
        <section class="escenario" aria-label="Explora el cementerio">
            <picture>
                <source
                    media="(max-aspect-ratio: 1/1)"
                    srcset="assets/img/fondos/cementerio-principal-movil.jpg"
                    width="1024"
                    height="1536"
                >
                <img
                    class="escenario-imagen"
                    src="assets/img/fondos/cementerio-principal.jpg"
                    alt=""
                    width="1673"
                    height="940"
                    fetchpriority="high"
                >
            </picture>

            <a
                class="acceso acceso-cluedo"
                href="juego.php"
            >
                <h2>Asesinato en la mansión del código</h2>
                <span class="indicacion">Entrar en la mansión</span>
            </a>

            <a
                class="acceso acceso-recetario"
                href="recetario.php"
            >
                <h2>
                    <span class="etiqueta-codigo">&lt;recetas&gt;</span>
                    <span>malditas</span>
                    <span class="etiqueta-codigo">&lt;/recetas&gt;</span>
                </h2>
                <span class="indicacion">Abrir recetario</span>
            </a>

            <a
                class="acceso acceso-ranking"
                href="ranking.php"
            >
                <h2>Top asesinos</h2>
                <span class="consulta">ORDER BY muertes DESC</span>
                <span class="indicacion">Ver ranking</span>
            </a>
        </section>
    </main>

    <footer class="pie">
        <button type="button" class="enlace-pie" id="abrir-sobre-nosotros">
            Sobre nosotros
        </button>

        <a class="enlace-pie" href="contacto.php">Contacta con Jack</a>

        <img
            class="logo-atlantida"
            src="assets/img/logos/logo-atlantida-terrorifico.jpg"
            alt="Atlántida Formación Profesional"
        >
    </footer>

    <?php if ($sesion !== null): ?>
        <dialog id="confirmar-salir" class="confirmar-salir" aria-labelledby="titulo-salir">
            <form class="confirmar-salir__interior" method="post" action="salir.php">
                <?= campoCsrf() ?>
                <p id="titulo-salir">¿De verdad quieres salir del cementerio?</p>

                <div class="confirmar-salir__acciones">
                    <button type="button" class="boton-quedarme" id="cancelar-salir" autofocus>
                        Prefiero quedarme
                    </button>
                    <button type="submit" class="boton-salir">Cerrar sesión</button>
                </div>
            </form>
        </dialog>
    <?php endif; ?>

    <dialog id="sobre-nosotros" class="modal-sobre" aria-labelledby="titulo-sobre">
        <div class="modal-interior">
            <button
                type="button"
                class="cerrar-modal"
                id="cerrar-sobre-nosotros"
                aria-label="Cerrar"
            >×</button>

            <div class="equipo">
                <figure class="miembro">
                    <img src="assets/img/equipo/sergio.jpg" alt="Foto de Sergio">
                    <figcaption>Sergio</figcaption>
                </figure>

                <div class="texto-sobre">
                    <h2 id="titulo-sobre">Sobre nosotros</h2>
                    <p>
                        Somos Sergio y Antonio, estudiantes de 2.º DAW en
                        Atlántida Formación Profesional. Hemos creado esta
                        página para demostrar nuestra capacidad de unir todas
                        las asignaturas dadas en el curso.
                    </p>
                </div>

                <figure class="miembro">
                    <img src="assets/img/equipo/antonio.jpg" alt="Foto de Antonio">
                    <figcaption>Antonio</figcaption>
                </figure>
            </div>
        </div>
    </dialog>

    <script src="assets/js/cursor-arma.js" defer></script>
    <script src="assets/js/principal.js"></script>
</body>
</html>