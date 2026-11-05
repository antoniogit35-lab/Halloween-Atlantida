<?php
declare(strict_types=1);

/**
 * Contacta con Jack (front).
 * Formulario con asunto, email y mensaje. De momento solo se encarga del aspecto
 * y de la validación en el navegador (assets/js/contacto.js).
 * El envío del correo (mail() + SMTP de Yahoo, ver enunciado: correo.php) se añadirá después.
 */

const URL_PRINCIPAL = 'principal.php';
const MAX_ASUNTO = 100;
const MAX_MENSAJE = 1000;

function escapar(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

/**
 * Estado del envío. Lo rellenará la parte de servidor cuando exista:
 *   'enviado' -> el correo salió bien
 *   'error'   -> el servidor no pudo enviarlo
 * Mientras tanto vale null y no se muestra ningún aviso.
 */
$estado = null;

// Si el servidor devuelve el formulario con un error, se conservan los datos escritos.
$asunto  = isset($_POST['asunto'])  && is_string($_POST['asunto'])  ? trim($_POST['asunto'])  : '';
$email   = isset($_POST['email'])   && is_string($_POST['email'])   ? trim($_POST['email'])   : '';
$mensaje = isset($_POST['mensaje']) && is_string($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contacta con Jack · Halloween Atlántida</title>
    <script>document.documentElement.classList.add('js');</script>
    <link rel="stylesheet" href="assets/css/contacto.css">
</head>
<body class="contacto-pagina">
    <main class="contacto">
        <a class="contacto__volver" href="<?= escapar(URL_PRINCIPAL) ?>">
            <span aria-hidden="true">←</span> Volver a página principal
        </a>

        <section class="lapida" aria-labelledby="contacto-titulo">
            <h1 class="lapida__titulo" id="contacto-titulo">Contacta con Jack</h1>
            <p class="lapida__intro">
                Cuéntale a Jack Skellington tus peores pesadillas.
                Todos los campos son obligatorios.
            </p>

<?php if ($estado === 'enviado'): ?>
            <p class="aviso aviso--ok" role="status">Tu pesadilla ya viaja hacia Jack. Puede tardar en responder.</p>
<?php elseif ($estado === 'error'): ?>
            <p class="aviso aviso--error" role="alert">No hemos podido enviar el mensaje. Inténtalo de nuevo en unos minutos.</p>
<?php endif; ?>

            <form class="formulario" id="formulario-contacto" method="post" action="contacto.php" novalidate>
                <p class="aviso aviso--error" id="resumen-errores" role="alert" tabindex="-1" hidden></p>

                <div class="campo">
                    <label class="campo__etiqueta" for="asunto">Asunto</label>
                    <input class="campo__control" type="text" id="asunto" name="asunto"
                           maxlength="<?= MAX_ASUNTO ?>" autocomplete="off"
                           value="<?= escapar($asunto) ?>"
                           aria-describedby="error-asunto">
                    <p class="campo__error" id="error-asunto" hidden></p>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="email">Tu email</label>
                    <input class="campo__control" type="email" id="email" name="email"
                           autocomplete="email" inputmode="email"
                           value="<?= escapar($email) ?>"
                           aria-describedby="error-email">
                    <p class="campo__error" id="error-email" hidden></p>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="mensaje">Tu mensaje</label>
                    <textarea class="campo__control campo__control--area" id="mensaje" name="mensaje"
                              rows="6" maxlength="<?= MAX_MENSAJE ?>"
                              aria-describedby="error-mensaje contador-mensaje"><?= escapar($mensaje) ?></textarea>
                    <p class="campo__error" id="error-mensaje" hidden></p>
                    <p class="campo__contador" id="contador-mensaje" data-max="<?= MAX_MENSAJE ?>">
                        0 / <?= MAX_MENSAJE ?>
                    </p>
                </div>

                <button class="boton" type="submit">Enviar pesadilla</button>
            </form>
        </section>
    </main>

    <script src="assets/js/contacto.js" defer></script>
</body>
</html>