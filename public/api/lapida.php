<?php
declare(strict_types=1);
require __DIR__ . '/../../app/usuarios.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$usuario = $_GET['usuario'] ?? '';

// Sin nombre válido no hay nada que consultar
if (!is_string($usuario) || trim($usuario) === '' || mb_strlen($usuario) > 30) {
    echo json_encode(['existe' => false]);
    exit;
}

try {
    $datos = datosLapida($usuario);
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['existe' => false]);
    exit;
}

echo json_encode($datos === null ? ['existe' => false] : ['existe' => true] + $datos);