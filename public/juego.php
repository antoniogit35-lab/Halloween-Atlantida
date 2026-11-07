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
            <button type="button" class="alternar-panel" id="alternar-panel"
                    aria-expanded="true" aria-controls="panel">Ocultar panel</button>
        </div>
    </header>

    <main class="juego">
        <div class="zona-mapa">
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
        </div>

        <!-- Panel de investigación: en escritorio, columna a la derecha; en móvil, bajo el mapa -->
        <aside class="panel" id="panel" aria-label="Panel de investigación">
            <div class="pestanas" role="tablist" aria-label="Secciones del panel">
                <button type="button" role="tab" id="pestana-interrogar"
                        aria-controls="seccion-interrogar" aria-selected="true">Interrogar</button>
                <button type="button" role="tab" id="pestana-cuaderno"
                        aria-controls="seccion-cuaderno" aria-selected="false" tabindex="-1">Cuaderno</button>
            </div>

            <section class="seccion" id="seccion-interrogar" role="tabpanel"
                     aria-labelledby="pestana-interrogar">
                <form class="form-pregunta" id="form-pregunta">
                    <p class="ubicacion">Habitación: <strong id="habitacion-elegida">ninguna</strong></p>

                    <fieldset>
                        <legend>Interrogas a: <span class="elegido" id="elegido-interrogado">—</span></legend>
                        <div class="opciones" id="opciones-interrogado"></div>
                    </fieldset>

                    <fieldset>
                        <legend>Arma: <span class="elegido" id="elegido-arma">—</span></legend>
                        <div class="opciones" id="opciones-arma"></div>
                    </fieldset>

                    <fieldset>
                        <legend>Sospechoso: <span class="elegido" id="elegido-sospechoso">—</span></legend>
                        <div class="opciones" id="opciones-sospechoso"></div>
                    </fieldset>

                    <button type="submit" class="boton-preguntar" id="boton-preguntar">Preguntar</button>
                </form>

                <div class="respuesta" id="respuesta" aria-live="polite"></div>
            </section>

            <section class="seccion" id="seccion-cuaderno" role="tabpanel"
                     aria-labelledby="pestana-cuaderno" hidden>
                <label class="etiqueta-notas" for="notas">Notas</label>
                <textarea class="notas" id="notas" rows="6" maxlength="2000"
                          placeholder="Por ejemplo: Chucky no tiene el cuchillo ni el baño"></textarea>

                <div class="listas-tachar">
                    <fieldset>
                        <legend>Sospechosos</legend>
                        <ul class="lista-tachar" id="lista-sospechoso"></ul>
                    </fieldset>

                    <fieldset>
                        <legend>Armas</legend>
                        <ul class="lista-tachar" id="lista-arma"></ul>
                    </fieldset>

                    <fieldset>
                        <legend>Habitaciones</legend>
                        <ul class="lista-tachar" id="lista-habitacion"></ul>
                    </fieldset>
                </div>
            </section>
        </aside>
    </main>

    <!-- El orden importa: datos, lógica, pruebas provisionales e interfaz -->
    <script src="assets/js/juego-datos.js" defer></script>
    <script src="assets/js/juego-logica.js" defer></script>
    <script src="assets/js/juego-pruebas.js" defer></script>
    <script src="assets/js/juego.js" defer></script>
</body>
</html>