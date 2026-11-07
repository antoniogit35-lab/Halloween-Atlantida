<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asesinato en la mansión del código · Enterrados en código</title>
    <link rel="stylesheet" href="assets/css/juego.css">
</head>
<body>
    <header class="cabecera">
        <a class="volver" href="principal.php">
            <span aria-hidden="true">←</span> Volver al cementerio
        </a>

        <h1>Asesinato en la mansión del código</h1>

        <div class="marcadores">
            <p class="marcador">Turnos <strong id="turnos"></strong></p>
            <p class="marcador">Intentos <strong id="intentos"></strong></p>
        </div>
    </header>

    <main class="juego">
        <section class="escenario" aria-label="Plano de la mansión">
            <img
                class="escenario-imagen"
                src="assets/img/fondos/mansion-mapa.jpg"
                alt="Plano de la mansión visto desde arriba, con seis habitaciones"
                width="1672"
                height="941"
                fetchpriority="high"
            >

            <!-- Una zona pulsable por habitación; el data-habitacion coincide con el id de juego-datos.js -->
            <button type="button" class="sala" data-habitacion="biblioteca" aria-pressed="false">
                <span class="sala-nombre">Biblioteca</span>
            </button>
            <button type="button" class="sala" data-habitacion="dormitorio" aria-pressed="false">
                <span class="sala-nombre">Dormitorio</span>
            </button>
            <button type="button" class="sala" data-habitacion="bano" aria-pressed="false">
                <span class="sala-nombre">Baño</span>
            </button>
            <button type="button" class="sala" data-habitacion="salon" aria-pressed="false">
                <span class="sala-nombre">Salón</span>
            </button>
            <button type="button" class="sala" data-habitacion="comedor" aria-pressed="false">
                <span class="sala-nombre">Comedor</span>
            </button>
            <button type="button" class="sala" data-habitacion="vestibulo" aria-pressed="false">
                <span class="sala-nombre">Vestíbulo</span>
            </button>

            <p class="estado-juego" id="estado-juego" role="status">
                Elige una habitación para empezar a investigar.
            </p>
        </section>
    </main>

    <!-- El orden importa: datos, lógica, pruebas provisionales e interfaz -->
    <script src="assets/js/juego-datos.js" defer></script>
    <script src="assets/js/juego-logica.js" defer></script>
    <script src="assets/js/juego-pruebas.js" defer></script>
    <script src="assets/js/juego.js" defer></script>
</body>
</html>