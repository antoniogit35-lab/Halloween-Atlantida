<?php
declare(strict_types=1);

// Con este modo, un fallo SQL lanza una excepción en lugar de pasar desapercibido
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * Devuelve una única conexión reutilizable a la base de datos.
 */
function conectar(): mysqli
{
    static $db = null;

    if ($db === null) {
        $c = require __DIR__ . '/config.php';
        $db = new mysqli($c['host'], $c['usuario'], $c['clave'], $c['base']);
        $db->set_charset('utf8mb4');
    }

    return $db;
}