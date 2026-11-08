<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/seguridad.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

// Debe ejecutarse antes de producir cualquier salida HTML.
$usuario = usuarioActual();
if ($usuario === null) {
    redirigir('login.php');
}
header('Cache-Control: no-store');

const URL_PRINCIPAL = 'principal.php';
const MAX_ASUNTO = 100;
const MIN_MENSAJE = 10;
const MAX_MENSAJE = 1000;

// Reutilizamos textoCampo() y escapar() del proyecto, sin redefinirlas.
// El email procede siempre de la sesión: nunca confiamos en $_POST['email'].
$email = textoCampo($usuario, 'email');
$asunto = textoCampo($_POST, 'asunto');
$mensaje = textoCampo($_POST, 'mensaje');
$esPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
$errores = ['asunto' => '', 'email' => '', 'mensaje' => ''];
$resumen = '';
$aviso = '';

if ($esPost) {
    if (!csrfValido()) {
        $resumen = 'No se pudo validar el formulario. Recarga la página e inténtalo otra vez.';
    } else {
        if ($asunto === '') {
            $errores['asunto'] = 'Escribe un asunto.';
        } elseif (mb_strlen($asunto, 'UTF-8') > MAX_ASUNTO) {
            $errores['asunto'] = 'El asunto no puede superar los ' . MAX_ASUNTO . ' caracteres.';
        } elseif (strpbrk($asunto, "\r\n") !== false) {
            $errores['asunto'] = 'El asunto debe estar en una sola línea.';
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errores['email'] = 'El email de tu cuenta no es válido. Revisa los datos de tu cuenta.';
        }

        $longitud = mb_strlen($mensaje, 'UTF-8');
        if ($mensaje === '') {
            $errores['mensaje'] = 'Escribe tu mensaje.';
        } elseif ($longitud < MIN_MENSAJE) {
            $errores['mensaje'] = 'El mensaje es muy corto. Escribe al menos ' . MIN_MENSAJE . ' caracteres.';
        } elseif ($longitud > MAX_MENSAJE) {
            $errores['mensaje'] = 'El mensaje no puede superar los ' . MAX_MENSAJE . ' caracteres.';
        }

        if (count(array_filter($errores)) > 0) {
            $resumen = 'Revisa los campos marcados antes de enviar.';
        } else {
            try {
                $configuracion = require __DIR__ . '/../app/config.php';

                $destinatario = $configuracion['correo_destinatario'] ?? '';
                $remitente = $configuracion['correo_remitente'] ?? '';
                $nombreRemitente = $configuracion['correo_remitente_nombre'] ?? 'Contacta con Jack';
                $smtpHost = $configuracion['smtp_host'] ?? '';
                $smtpPuerto = (int) ($configuracion['smtp_puerto'] ?? 587);
                $smtpUsuario = $configuracion['smtp_usuario'] ?? '';
                $smtpClave = $configuracion['smtp_clave'] ?? '';

                if (
                    $destinatario === '' ||
                    $remitente === '' ||
                    $smtpHost === '' ||
                    $smtpUsuario === '' ||
                    $smtpClave === ''
                ) {
                    throw new RuntimeException('La configuración de correo no está completa.');
                }

                $correo = new PHPMailer(true);
                $correo->isSMTP();
                $correo->Host = $smtpHost;
                $correo->SMTPAuth = true;
                $correo->Username = $smtpUsuario;
                $correo->Password = $smtpClave;
                $correo->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $correo->Port = $smtpPuerto;
                $correo->CharSet = 'UTF-8';

                $correo->setFrom($remitente, $nombreRemitente);
                $correo->addAddress($destinatario);
                $correo->addReplyTo($email, $usuario['nombre'] ?? $email);

                $correo->Subject = $asunto;
                $correo->Body = "Has recibido un nuevo mensaje desde Contacta con Jack.\n\n"
                    . "De: {$email}\n\n"
                    . "Mensaje:\n{$mensaje}\n";
                $correo->AltBody = $correo->Body;

                $correo->send();

                $aviso = 'Tu mensaje se ha enviado correctamente. ¡Gracias por escribir a Jack!';
                $asunto = '';
                $mensaje = '';
            } catch (Throwable $e) {
                $aviso = 'No se ha podido enviar el mensaje. Inténtalo de nuevo más tarde.';
                error_log('Error al enviar correo de contacto: ' . $e->getMessage());
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contacta con Jack</title>
    <link rel="stylesheet" href="assets/css/contacto.css">
    <script src="assets/js/contacto.js" defer></script>
    <script src="assets/js/cursor-arma.js" defer></script>
</head>
<body class="contacto-pagina">
    <a class="saltar" href="#formulario-contacto">Ir al formulario</a>

    <header class="cabecera">
        <a class="cabecera__volver" href="<?= escapar(URL_PRINCIPAL) ?>">
            <span aria-hidden="true">←</span>
            Volver a la página principal
        </a>
        <h1 id="contacto-titulo">Contacta con Jack</h1>
    </header>

    <main class="contacto">
        <section class="escena" aria-labelledby="contacto-titulo">
            <!-- Usar la nueva imagen adjunta, completa y sin recortarla. -->
            <img
                class="escena__imagen"
                src="assets/img/jack-contacto.png"
                alt=""
                width="1672"
                height="941"
                fetchpriority="high"
                decoding="async"
            >

            <!-- La imagen ya incluye a Jack, el ordenador y la decoración. -->
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

<?php if ($aviso !== ''): ?>
                    <p class="aviso" role="status">
                        <?= escapar($aviso) ?>
                    </p>
<?php endif; ?>

                    <form
                        class="formulario"
                        id="formulario-contacto"
                        method="post"
                        action="contacto.php"
                        novalidate
                    >
                        <?= campoCsrf() ?>
                        <p
                            class="aviso aviso--error"
                            id="resumen-errores"
                            role="alert"
                            tabindex="-1"
                            <?= $resumen === '' ? 'hidden' : '' ?>
                        ><?= escapar($resumen) ?></p>

                        <div class="campo">
                            <label class="campo__etiqueta" for="asunto">
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
                                aria-invalid="<?= $errores['asunto'] === '' ? 'false' : 'true' ?>"
                                required
                            >
                            <p
                                class="campo__error"
                                id="error-asunto"
                                <?= $errores['asunto'] === '' ? 'hidden' : '' ?>
                            ><?= escapar($errores['asunto']) ?></p>
                        </div>

                        <div class="campo">
                            <label class="campo__etiqueta" for="email">
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
                                aria-invalid="<?= $errores['email'] === '' ? 'false' : 'true' ?>"
                                title="Email de la cuenta con la que has iniciado sesión."
                                readonly
                                required
                            >
                            <p
                                class="campo__error"
                                id="error-email"
                                <?= $errores['email'] === '' ? 'hidden' : '' ?>
                            ><?= escapar($errores['email']) ?></p>
                        </div>

                        <div class="campo campo--ancho">
                            <label class="campo__etiqueta" for="mensaje">
                                Tu mensaje
                            </label>
                            <textarea
                                class="campo__control campo__control--area"
                                id="mensaje"
                                name="mensaje"
                                rows="3"
                                maxlength="<?= MAX_MENSAJE ?>"
                                aria-describedby="error-mensaje contador-mensaje"
                                aria-invalid="<?= $errores['mensaje'] === '' ? 'false' : 'true' ?>"
                                required
                            ><?= escapar($mensaje) ?></textarea>
                            <div class="campo__informacion">
                                <p
                                    class="campo__error"
                                    id="error-mensaje"
                                    <?= $errores['mensaje'] === '' ? 'hidden' : '' ?>
                                ><?= escapar($errores['mensaje']) ?></p>
                                <p
                                    class="campo__contador"
                                    id="contador-mensaje"
                                    data-max="<?= MAX_MENSAJE ?>"
                                >Máximo <?= MAX_MENSAJE ?> caracteres</p>
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
        <span>Oficina de correspondencia del otro mundo</span>
    </footer>
</body>
</html>