<?php
declare(strict_types=1);
require __DIR__ . '/../app/seguridad.php';

if (usuarioActual() !== null) {
    redirigir('principal.php');
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

            $resultado = altaUsuario($_POST);
            $errores = $resultado['errores'];

            if ($clave !== $clave2) {
                $errores['clave2'] = 'Las contraseñas no coinciden.';
            }

            if ($resultado['ok'] && !isset($errores['clave2'])) {
                // Tras registrarse, entra directamente
                loginUsuario(trim((string) $_POST['usuario']), $clave);
                redirigir('principal.php');
            }

            // Si las contraseñas no coincidían, el usuario ya se creó: se avisa
            if ($resultado['ok']) {
                $errores = ['usuario' => 'Ese usuario ya está registrado.'];
            }
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        $errorGeneral = 'No se pudo completar la operación. Inténtalo más tarde.';
    }
}

$asesinos = listaAsesinos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear asesinado · Enterrados en código</title>
</head>
<body class="pagina-registro">
    <a class="volver" href="login.php">← Volver</a>

    <main>
        <h1>Crear asesinado</h1>

        <?php if ($errorGeneral !== ''): ?>
            <p class="error" role="alert"><?= escapar($errorGeneral) ?></p>
        <?php endif; ?>

        <form method="post" action="registro.php" novalidate>
            <?= campoCsrf() ?>

            <label for="usuario">Nombre</label>
            <input id="usuario" name="usuario" type="text" required
                   autocomplete="username" value="<?= valorPost('usuario') ?>">
            <?= errorCampo($errores, 'usuario') ?>

            <label for="email">Email</label>
            <input id="email" name="email" type="email" required
                   autocomplete="email" value="<?= valorPost('email') ?>">
            <?= errorCampo($errores, 'email') ?>

            <label for="clave">Contraseña</label>
            <input id="clave" name="clave" type="password" required
                   autocomplete="new-password">
            <?= errorCampo($errores, 'clave') ?>

            <label for="clave2">Repite la contraseña</label>
            <input id="clave2" name="clave2" type="password" required
                   autocomplete="new-password">
            <?= errorCampo($errores, 'clave2') ?>

            <label for="fecha_nacimiento">Fecha de nacimiento</label>
            <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" required
                   min="1900-01-01" max="<?= date('Y-m-d') ?>"
                   value="<?= valorPost('fecha_nacimiento') ?>">
            <?= errorCampo($errores, 'fecha_nacimiento') ?>

            <label for="asesino">Asesinado por</label>
            <select id="asesino" name="asesino" required>
                <option value="">Elige a tu asesino</option>
                <?php foreach ($asesinos as $id => $nombre): ?>
                    <option value="<?= escapar($id) ?>"
                        <?= (($_POST['asesino'] ?? '') === $id) ? 'selected' : '' ?>>
                        <?= escapar($nombre) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= errorCampo($errores, 'asesino') ?>

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

            <label for="respuesta">Respuesta</label>
            <input id="respuesta" name="respuesta" type="text" required
                   autocomplete="off" value="<?= valorPost('respuesta') ?>">
            <?= errorCampo($errores, 'respuesta') ?>

            <button type="submit">Crear usuario</button>
        </form>
    </main>
</body>
</html>