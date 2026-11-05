<?php
declare(strict_types=1);

const URL_PRINCIPAL = 'principal.php';
const MAX_ASUNTO = 100;
const MAX_MENSAJE = 1000;

function escapar(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

/*
 * El envío real del correo todavía está pendiente.
 * Estos valores conservan los datos después de un POST.
 */
$estado = null;

$asunto = isset($_POST['asunto']) && is_string($_POST['asunto'])
    ? trim($_POST['asunto'])
    : '';

$email = isset($_POST['email']) && is_string($_POST['email'])
    ? trim($_POST['email'])
    : '';

$mensaje = isset($_POST['mensaje']) && is_string($_POST['mensaje'])
    ? trim($_POST['mensaje'])
    : '';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Contacta con Jack · Halloween Atlántida</title>

    <link rel="stylesheet" href="assets/css/contacto.css">
    <script src="assets/js/contacto.js" defer></script>
</head>

<body class="contacto-pagina">
    <header class="cabecera">
        <a
            class="cabecera__volver"
            href="<?= escapar(URL_PRINCIPAL) ?>"
        >
            <span aria-hidden="true">←</span>
            Volver a la página principal
        </a>

        <h1 id="contacto-titulo">Contacta con Jack</h1>

        <p class="cabecera__marca">Halloween Atlántida</p>
    </header>

    <main class="contacto">
        <section class="escena" aria-labelledby="contacto-titulo">
            <!--
                La imagen ya contiene el ordenador, Jack
                y todas las decoraciones.
            -->
            <img
                class="escena__imagen"
                src="assets/img/jack-contacto.png"
                alt=""
                width="1672"
                height="941"
                fetchpriority="high"
            >

            <!-- Único elemento superpuesto: el formulario real. -->
            <div class="pantalla">
                <div class="pantalla__contenido">
                    <header class="correo-cabecera">
                        <p class="correo-cabecera__detalle">
                            Correspondencia del otro mundo
                        </p>

                        <h2>Correo para Jack</h2>

                        <p class="correo-cabecera__intro">
                            Cuéntale tus peores pesadillas.
                            Todos los campos son obligatorios.
                        </p>
                    </header>

<?php if ($estado === 'enviado'): ?>
                    <p class="aviso aviso--ok" role="status">
                        Tu pesadilla ya viaja hacia Jack.
                    </p>
<?php elseif ($estado === 'error'): ?>
                    <p class="aviso aviso--error" role="alert">
                        No hemos podido enviar el mensaje.
                        Inténtalo de nuevo en unos minutos.
                    </p>
<?php endif; ?>

                    <form
                        class="formulario"
                        id="formulario-contacto"
                        method="post"
                        action="contacto.php"
                        novalidate
                    >
                        <p
                            class="aviso aviso--error"
                            id="resumen-errores"
                            role="alert"
                            tabindex="-1"
                            hidden
                        ></p>

                        <div class="campo">
                            <label
                                class="campo__etiqueta"
                                for="asunto"
                            >
                                Asunto
                            </label>

                            <input
                                class="campo__control"
                                type="text"
                                id="asunto"
                                name="asunto"
                                maxlength="<?= MAX_ASUNTO ?>"
                                autocomplete="off"
                                value="<?= escapar($asunto) ?>"
                                aria-describedby="error-asunto"
                                required
                            >

                            <p
                                class="campo__error"
                                id="error-asunto"
                                hidden
                            ></p>
                        </div>

                        <div class="campo">
                            <label
                                class="campo__etiqueta"
                                for="email"
                            >
                                Tu email
                            </label>

                            <input
                                class="campo__control"
                                type="email"
                                id="email"
                                name="email"
                                autocomplete="email"
                                inputmode="email"
                                value="<?= escapar($email) ?>"
                                aria-describedby="error-email"
                                required
                            >

                            <p
                                class="campo__error"
                                id="error-email"
                                hidden
                            ></p>
                        </div>

                        <div class="campo">
                            <label
                                class="campo__etiqueta"
                                for="mensaje"
                            >
                                Tu mensaje
                            </label>

                            <textarea
                                class="campo__control campo__control--area"
                                id="mensaje"
                                name="mensaje"
                                rows="4"
                                maxlength="<?= MAX_MENSAJE ?>"
                                aria-describedby="error-mensaje contador-mensaje"
                                required
                            ><?= escapar($mensaje) ?></textarea>

                            <div class="campo__informacion">
                                <p
                                    class="campo__error"
                                    id="error-mensaje"
                                    hidden
                                ></p>

                                <p
                                    class="campo__contador"
                                    id="contador-mensaje"
                                    data-max="<?= MAX_MENSAJE ?>"
                                >
                                    Máximo <?= MAX_MENSAJE ?> caracteres
                                </p>
                            </div>
                        </div>

                        <button class="boton" type="submit">
                            Enviar pesadilla
                            <span aria-hidden="true">→</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer class="pie">
        <span>Halloween Atlántida</span>
        <span>Oficina de correspondencia del otro mundo</span>
    </footer>
</body>
</html>