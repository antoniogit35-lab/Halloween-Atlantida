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
</head>
<body class="pagina-recuperar">
    <a class="volver" href="login.php">← Volver</a>

    <main>
        <h1>Cambiar contraseña</h1>

        <?php if ($error !== ''): ?>
            <p class="error" role="alert"><?= escapar($error) ?></p>
        <?php endif; ?>

        <?php if ($paso === 1): ?>
            <form method="post" action="recuperar.php">
                <?= campoCsrf() ?>
                <input type="hidden" name="paso" value="1">

                <label for="usuario">Nombre</label>
                <input id="usuario" name="usuario" type="text" required
                       autocomplete="username" value="<?= escapar($usuario) ?>">

                <button type="submit">Continuar</button>
            </form>
        <?php else: ?>
            <form method="post" action="recuperar.php">
                <?= campoCsrf() ?>
                <input type="hidden" name="paso" value="2">
                <input type="hidden" name="usuario" value="<?= escapar($usuario) ?>">

                <p><?= escapar($pregunta) ?></p>

                <label for="respuesta">Respuesta</label>
                <input id="respuesta" name="respuesta" type="text" required autocomplete="off">

                <label for="clave">Nueva contraseña</label>
                <input id="clave" name="clave" type="password" required autocomplete="new-password">

                <label for="clave2">Repite la contraseña</label>
                <input id="clave2" name="clave2" type="password" required autocomplete="new-password">

                <button type="submit">Cambiar contraseña</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>