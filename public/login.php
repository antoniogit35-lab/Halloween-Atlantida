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
    <link rel="stylesheet" href="assets/css/fuentes.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="pagina-login">
    <main class="escenario">
        <img
            class="escenario-imagen"
            src="assets/img/fondos/fondo-login.jpg"
            alt=""
            width="1672"
            height="941"
            fetchpriority="high"
        >

        <a class="volver" href="principal.php" aria-label="Volver a la página principal">←</a>

        <!-- Ficha del asesino, sobre el cartel -->
        <aside class="ficha-asesino" aria-live="polite">
            <h2 id="ficha-nombre">Elige a tu asesino</h2>
            <p id="ficha-obra" class="ficha-obra"></p>
            <p id="ficha-descripcion" class="ficha-descripcion"></p>
        </aside>

        <!-- Lápida con el formulario -->
        <section class="lapida">
            <h1 class="solo-lectores">Iniciar sesión</h1>
            <p class="lapida-titulo">Asesinado por</p>

            <button type="button" class="foto-asesino" id="abrir-asesinos"
                    aria-haspopup="dialog" aria-label="Ver todos los asesinos">
                <img id="foto-asesino" src="" alt="" hidden>
                <span id="foto-interrogante" aria-hidden="true">?</span>
            </button>

            <form class="lapida-form" method="post" action="login.php">
                <?= campoCsrf() ?>

                <?php if ($aviso !== ''): ?>
                    <p class="mensaje aviso" role="status"><?= escapar($aviso) ?></p>
                <?php endif; ?>
                <?php if ($error !== ''): ?>
                    <p class="mensaje error" role="alert"><?= escapar($error) ?></p>
                <?php endif; ?>

                <div class="campo">
                    <label for="usuario">Nombre</label>
                    <input id="usuario" name="usuario" type="text" required
                           autocomplete="username" value="<?= escapar($usuario) ?>">
                </div>

                    <div class="campo">
                    <label for="clave">Contraseña</label>
                    <input id="clave" name="clave" type="password" required
                           autocomplete="current-password">
                </div>

                <p class="epitafio" id="epitafio"></p>
                <p class="fechas" id="fechas"></p>

                <button class="entrar" type="submit">Entrar</button>
            </form>
        </section>

        <!-- Base de la lápida -->
        <nav class="pedestal" aria-label="Otras opciones de acceso">
            <a href="registro.php">Crear asesinado</a>
            <span aria-hidden="true">/</span>
            <a href="recuperar.php">Cambiar contraseña</a>
        </nav>

        <dialog id="modal-asesinos" class="modal-asesinos" aria-labelledby="titulo-modal-asesinos">
            <div class="modal-interior">
                <button type="button" class="cerrar-modal" id="cerrar-asesinos" aria-label="Cerrar">×</button>
                <h2 id="titulo-modal-asesinos">Elige a tu asesino</h2>
                <ul class="rejilla-asesinos" id="rejilla-asesinos"></ul>
            </div>
        </dialog>
    </main>

    <script src="assets/js/cursor-arma.js" defer></script>
    <script src="assets/js/login.js" defer></script>
</body>
</html>