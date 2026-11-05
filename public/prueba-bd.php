<?php
declare(strict_types=1);
require __DIR__ . '/../app/conexion.php';

try {
    $r = conectar()->query('SELECT COUNT(*) AS total FROM usuarios');
    echo 'Conexión correcta. Usuarios: ' . (int) $r->fetch_assoc()['total'];
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo 'No se pudo conectar con la base de datos.';
}