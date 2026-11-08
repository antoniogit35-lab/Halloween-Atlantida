<?php
declare(strict_types=1);
require __DIR__ . '/../app/seguridad.php';

if (usuarioActual() !== null) {
    redirigir('principal.php');
}

$paso = 1;
$usuario = '';
$pregunta = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = is_string($_POST['usuario'] ?? null) ? trim($_POST['usuario']) : '';
    $enviadoPaso = is_string($_POST['paso'] ?? null) ? $_POST['paso'] : '';

    try {
        if (!csrfValido()) {
            $error = 'La sesión ha caducado. Inténtalo de nuevo.';
        } else {
            // En ambos pasos hace falta conocer la pregunta del usuario
            $pregunta = preguntaDeUsuario($usuario) ?? '';

            if ($pregunta === '') {
                $error = 'No existe ningún usuario con ese nombre.';
            } elseif ($enviadoPaso === '1') {
                $paso = 2;
            } elseif ($enviadoPaso === '2') {
                $paso = 2;
                $respuesta = is_string($_POST['respuesta'] ?? null) ? $_POST['respuesta'] : '';
                $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';
                $clave2 = is_string($_POST['clave2'] ?? null) ? $_POST['clave2'] : '';

                if ($clave !== $clave2) {
                    $error = 'Las contraseñas no coinciden.';
                } else {
                    $resultado = cambioContrasena($usuario, $respuesta, $clave);
                    if ($resultado['ok']) {
                        redirigir('login.php?cambio=ok');
                    }
                    $error = $resultado['error'];
                }
            }
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        $error = 'No se pudo completar la operación. Inténtalo más tarde.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambiar contraseña · Enterrados en código</title>
    <link rel="stylesheet" href="assets/css/pergamino.css">
</head>
<body class="pagina-pergamino pagina-recuperar">
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
            <h1>Cambiar contraseña</h1>

            <?php if ($error !== ''): ?>
                <p class="error error-general" role="alert"><?= escapar($error) ?></p>
            <?php endif; ?>

            <?php if ($paso === 1): ?>
                <form class="form-recuperar" method="post" action="recuperar.php">
                    <?= campoCsrf() ?>
                    <input type="hidden" name="paso" value="1">

                    <div class="campo">
                        <label for="usuario">Nombre</label>
                        <input id="usuario" name="usuario" type="text" required
                               autocomplete="username" value="<?= escapar($usuario) ?>">
                    </div>

                    <button class="boton-pergamino" type="submit">Continuar</button>
                </form>
            <?php else: ?>
                <form class="form-recuperar" method="post" action="recuperar.php">
                    <?= campoCsrf() ?>
                    <input type="hidden" name="paso" value="2">
                    <input type="hidden" name="usuario" value="<?= escapar($usuario) ?>">

                    <p class="pregunta-seguridad"><?= escapar($pregunta) ?></p>

                    <div class="campo">
                        <label for="respuesta">Respuesta</label>
                        <input id="respuesta" name="respuesta" type="text" required autocomplete="off">
                    </div>

                    <div class="campo">
                        <label for="clave">Nueva contraseña</label>
                        <input id="clave" name="clave" type="password" required autocomplete="new-password">
                    </div>

                    <div class="campo">
                        <label for="clave2">Repite la contraseña</label>
                        <input id="clave2" name="clave2" type="password" required autocomplete="new-password">
                    </div>

                    <button class="boton-pergamino" type="submit">Cambiar contraseña</button>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <script src="assets/js/cursor-arma.js" defer></script>
</body>
</html>