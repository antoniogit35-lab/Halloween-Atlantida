<?php
declare(strict_types=1);
require __DIR__ . '/../app/seguridad.php';

if (usuarioActual() !== null) {
    redirigir('principal.php');
}

/**
 * Une día, mes y año en formato AAAA-MM-DD.
 * Devuelve '' si no forman una fecha real.
 */
function fechaDesdePartes(array $datos): string
{
    $dia = textoCampo($datos, 'dia');
    $mes = textoCampo($datos, 'mes');
    $anio = textoCampo($datos, 'anio');

    if (!ctype_digit($dia) || !ctype_digit($mes) || !ctype_digit($anio) || strlen($anio) !== 4) {
        return '';
    }

    if (!checkdate((int) $mes, (int) $dia, (int) $anio)) {
        return '';
    }

    return sprintf('%04d-%02d-%02d', (int) $anio, (int) $mes, (int) $dia);
}

$errores = [];
$errorGeneral = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!csrfValido()) {
            $errorGeneral = 'La sesión ha caducado. Inténtalo de nuevo.';
        } else {
            $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';
            $clave2 = is_string($_POST['clave2'] ?? null) ? $_POST['clave2'] : '';

            $datos = $_POST;
            $datos['fecha_nacimiento'] = fechaDesdePartes($_POST);

            // Se valida todo antes de tocar la base de datos
            $errores = validarAlta($datos);
            if ($clave !== $clave2) {
                $errores['clave2'] = 'Las contraseñas no coinciden.';
            }

            if (!$errores) {
                $resultado = altaUsuario($datos);
                $errores = $resultado['errores'];

                if ($resultado['ok']) {
                    // Tras registrarse, entra directamente
                    loginUsuario(trim((string) $_POST['usuario']), $clave);
                    redirigir('principal.php');
                }
            }
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        $errorGeneral = 'No se pudo completar la operación. Inténtalo más tarde.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear asesinado · Enterrados en código</title>
    <link rel="stylesheet" href="assets/css/pergamino.css">
</head>
<body class="pagina-pergamino pagina-registro">
    <main class="escenario">
        <img
            class="escenario-imagen"
            src="assets/img/fondos/fondo-crear-recuperar.jpg"
            alt=""
            width="1672"
            height="941"
            fetchpriority="high"
        >

        <a class="volver" href="login.php" aria-label="Volver al inicio de sesión">←</a>

        <section class="pergamino">
            <h1>Crear asesinado</h1>

            <?php if ($errorGeneral !== ''): ?>
                <p class="error error-general" role="alert"><?= escapar($errorGeneral) ?></p>
            <?php endif; ?>

            <form class="form-registro" method="post" action="registro.php" novalidate>
                <?= campoCsrf() ?>

                <div class="campo">
                    <label for="usuario">Nombre</label>
                    <input id="usuario" name="usuario" type="text" required
                           autocomplete="username" value="<?= valorPost('usuario') ?>">
                    <?= errorCampo($errores, 'usuario') ?>
                </div>

                <div class="campo">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" required
                           autocomplete="email" value="<?= valorPost('email') ?>">
                    <?= errorCampo($errores, 'email') ?>
                </div>

                <div class="campo">
                    <label for="clave">Contraseña</label>
                    <input id="clave" name="clave" type="password" required
                           autocomplete="new-password">
                    <?= errorCampo($errores, 'clave') ?>
                </div>

                <div class="campo">
                    <label for="clave2">Repite la contraseña</label>
                    <input id="clave2" name="clave2" type="password" required
                           autocomplete="new-password">
                    <?= errorCampo($errores, 'clave2') ?>
                </div>

                <fieldset class="campo">
                    <legend>Fecha de nacimiento</legend>
                    <div class="fecha-partes">
                        <input id="dia" name="dia" type="text" inputmode="numeric" maxlength="2"
                               placeholder="DD" aria-label="Día" autocomplete="bday-day"
                               value="<?= valorPost('dia') ?>">
                        <input id="mes" name="mes" type="text" inputmode="numeric" maxlength="2"
                               placeholder="MM" aria-label="Mes" autocomplete="bday-month"
                               value="<?= valorPost('mes') ?>">
                        <input id="anio" name="anio" type="text" inputmode="numeric" maxlength="4"
                               placeholder="AAAA" aria-label="Año" autocomplete="bday-year"
                               value="<?= valorPost('anio') ?>">
                    </div>
                    <?= errorCampo($errores, 'fecha_nacimiento') ?>
                </fieldset>

                <div class="campo">
                    <span class="etiqueta">Asesinado por</span>
                    <button type="button" class="elegir-asesino" id="abrir-asesinos"
                            aria-haspopup="dialog">
                        <span class="foto-mini">
                            <img id="foto-asesino" src="" alt="" hidden>
                            <span id="foto-interrogante" aria-hidden="true">?</span>
                        </span>
                        <span id="nombre-asesino">Elige a tu asesino</span>
                    </button>
                    <input type="hidden" id="asesino" name="asesino" value="<?= valorPost('asesino') ?>">
                    <noscript><p class="error">Activa JavaScript para elegir a tu asesino.</p></noscript>
                    <?= errorCampo($errores, 'asesino') ?>
                </div>

                <div class="campo">
                    <label for="pregunta">Pregunta de seguridad</label>
                    <select id="pregunta" name="pregunta" required>
                        <option value="">Elige una pregunta</option>
                        <?php foreach (PREGUNTAS_SEGURIDAD as $indice => $texto): ?>
                            <option value="<?= $indice ?>"
                                <?= (($_POST['pregunta'] ?? '') === (string) $indice) ? 'selected' : '' ?>>
                                <?= escapar($texto) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= errorCampo($errores, 'pregunta') ?>
                </div>

                <div class="campo">
                    <label for="respuesta">Respuesta</label>
                    <input id="respuesta" name="respuesta" type="text" required
                           autocomplete="off" value="<?= valorPost('respuesta') ?>">
                    <?= errorCampo($errores, 'respuesta') ?>
                </div>

                <button class="boton-pergamino" type="submit">Crear usuario</button>
            </form>
        </section>

        <dialog id="modal-asesinos" class="modal-asesinos" aria-labelledby="titulo-modal-asesinos">
            <div class="modal-interior">
                <button type="button" class="cerrar-modal" id="cerrar-asesinos" aria-label="Cerrar">×</button>
                <h2 id="titulo-modal-asesinos">Elige a tu asesino</h2>
                <ul class="rejilla-asesinos" id="rejilla-asesinos"></ul>
            </div>
        </dialog>
    </main>

    <script src="assets/js/cursor-arma.js" defer></script>
    <script src="assets/js/registro.js" defer></script>
</body>
</html>