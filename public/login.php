<?php
declare(strict_types=1);
require __DIR__ . '/../app/seguridad.php';

// Si ya hay sesión iniciada, no tiene sentido ver el login
if (usuarioActual() !== null) {
    redirigir('principal.php');
}

$error = '';
$usuario = '';
$aviso = ($_GET['cambio'] ?? '') === 'ok' ? 'Contraseña actualizada. Ya puedes entrar.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = is_string($_POST['usuario'] ?? null) ? trim($_POST['usuario']) : '';
    $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';

    try {
        if (!csrfValido()) {
            $error = 'La sesión ha caducado. Inténtalo de nuevo.';
        } elseif (loginUsuario($usuario, $clave) === null) {
            // No se indica cuál de los dos datos falló
            $error = 'Usuario o contraseña incorrectos.';
        } else {
            redirigir('principal.php');
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
    <title>Iniciar sesión · Enterrados en código</title>
</head>
<body class="pagina-login">
    <a class="volver" href="principal.php">← Volver</a>

    <main>
        <h1>Iniciar sesión</h1>

        <?php if ($aviso !== ''): ?>
            <p class="aviso" role="status"><?= escapar($aviso) ?></p>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <p class="error" role="alert"><?= escapar($error) ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <?= campoCsrf() ?>

            <label for="usuario">Nombre</label>
            <input id="usuario" name="usuario" type="text" required
                   autocomplete="username" value="<?= escapar($usuario) ?>">

            <label for="clave">Contraseña</label>
            <input id="clave" name="clave" type="password" required
                   autocomplete="current-password">

            <button type="submit">Entrar</button>
        </form>

        <p>
            <a href="registro.php">Crear asesinado</a> ·
            <a href="recuperar.php">Cambiar contraseña</a>
        </p>
    </main>
</body>
</html>