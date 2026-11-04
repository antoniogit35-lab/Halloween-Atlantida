<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enterrados en código</title>
    <link rel="stylesheet" href="assets/css/principal.css">
</head>
<body>
    <header class="cabecera">
        <div class="reserva-logo" aria-hidden="true"></div>

        <h1>Enterrados en código</h1>

        <div class="cabecera-acceso">
            <span class="sesion-pendiente">
                Iniciar sesión
                <small>Próximamente</small>
            </span>
        </div>
    </header>

    <main class="principal">
        <section class="escenario" aria-label="Explora el cementerio">
            <picture>
                <source
                    media="(max-aspect-ratio: 1/1)"
                    srcset="assets/img/fondos/cementerio-principal-movil.png"
                    width="1024"
                    height="1536"
                >
                <img
                    class="escenario-imagen"
                    src="assets/img/fondos/cementerio-principal.png"
                    alt=""
                    width="1673"
                    height="940"
                    fetchpriority="high"
                >
            </picture>

            <div class="acceso acceso-cluedo">
                <h2>Asesinato en la mansión del código</h2>
                <span class="estado">Próximamente</span>
            </div>

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

        <span>Contacta con Jack <small>· Próximamente</small></span>

        <img
            class="logo-atlantida"
            src="assets/img/logos/logo-atlantida-terrorifico.png"
            alt="Atlántida Formación Profesional"
        >
    </footer>

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

    <script src="assets/js/principal.js"></script>
</body>
</html>