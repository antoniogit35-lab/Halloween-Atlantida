<?php
declare(strict_types=1);
require __DIR__ . '/../app/seguridad.php';

cerrarSesion();
redirigir('principal.php');