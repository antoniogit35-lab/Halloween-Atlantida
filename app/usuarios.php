<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';

// Preguntas de seguridad que se pueden elegir al crear la cuenta
const PREGUNTAS_SEGURIDAD = [
    '¿Cuál es el arma de tu asesino favorito?',
    '¿Cómo se llamaba tu primera mascota?',
    '¿En qué calle creciste?',
    '¿Cuál es tu película de terror favorita?',
    '¿Cuál es tu comida favorita?',
];

/**
 * Inicia la sesión solo si todavía no está iniciada.
 */
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Devuelve el valor de un campo como texto limpio.
 * Si no existe o no es un texto (por ejemplo, un array), devuelve ''.
 */
function textoCampo(array $datos, string $campo): string
{
    $valor = $datos[$campo] ?? '';

    return is_string($valor) ? trim($valor) : '';
}

/**
 * Devuelve los id de los asesinos disponibles en personajes.json.
 */
function cargarIdsAsesinos(): array
{
    $json = @file_get_contents(__DIR__ . '/../public/data/personajes.json');
    $datos = $json !== false ? json_decode($json, true) : null;
    $ids = [];

    if (is_array($datos)) {
        foreach ($datos as $personaje) {
            if (is_array($personaje) && isset($personaje['id']) && is_string($personaje['id'])) {
                $ids[] = $personaje['id'];
            }
        }
    }

    return $ids;
}

/**
 * La respuesta de seguridad se compara sin mayúsculas ni espacios sobrantes.
 */
function normalizarRespuesta(string $respuesta): string
{
    return mb_strtolower(trim($respuesta), 'UTF-8');
}

/**
 * Comprueba los datos del formulario de alta.
 * Devuelve un array [campo => mensaje]; si está vacío, todo es válido.
 */
function validarAlta(array $datos): array
{
    $errores = [];

    $usuario = textoCampo($datos, 'usuario');
    if (!preg_match('/^[\p{L}\p{N}_.-]{3,30}$/u', $usuario)) {
        $errores['usuario'] = 'El nombre debe tener entre 3 y 30 caracteres (letras, números, _ . -).';
    }

    // La contraseña no se recorta: los espacios podrían formar parte de ella
    $clave = is_string($datos['clave'] ?? null) ? $datos['clave'] : '';
    if (mb_strlen($clave) < 8 || mb_strlen($clave) > 72) {
        $errores['clave'] = 'La contraseña debe tener entre 8 y 72 caracteres.';
    }

    $email = textoCampo($datos, 'email');
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 150) {
        $errores['email'] = 'Introduce un email válido.';
    }

    $pregunta = filter_var($datos['pregunta'] ?? null, FILTER_VALIDATE_INT);
    if ($pregunta === false || !isset(PREGUNTAS_SEGURIDAD[$pregunta])) {
        $errores['pregunta'] = 'Elige una pregunta de seguridad.';
    }

    $respuesta = textoCampo($datos, 'respuesta');
    if (mb_strlen($respuesta) < 2 || mb_strlen($respuesta) > 100) {
        $errores['respuesta'] = 'La respuesta debe tener entre 2 y 100 caracteres.';
    }

    if (!in_array(textoCampo($datos, 'asesino'), cargarIdsAsesinos(), true)) {
        $errores['asesino'] = 'Elige un asesino de la lista.';
    }

    // La fecha debe existir de verdad y no estar en el futuro
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', textoCampo($datos, 'fecha_nacimiento'));
    $hoy = new DateTimeImmutable('today');
    $minima = new DateTimeImmutable('1900-01-01');
    if ($fecha === false
        || $fecha->format('Y-m-d') !== textoCampo($datos, 'fecha_nacimiento')
        || $fecha > $hoy
        || $fecha < $minima) {
        $errores['fecha_nacimiento'] = 'Introduce una fecha de nacimiento válida.';
    }

    return $errores;
}

/**
 * Da de alta un usuario nuevo.
 * Devuelve ['ok' => bool, 'errores' => [campo => mensaje]].
 */
function altaUsuario(array $datos): array
{
    $errores = validarAlta($datos);
    if ($errores) {
        return ['ok' => false, 'errores' => $errores];
    }

    $usuario = textoCampo($datos, 'usuario');
    $hashClave = password_hash($datos['clave'], PASSWORD_DEFAULT);
    $email = textoCampo($datos, 'email');
    $perfil = 'user'; // el perfil nunca viene del formulario
    $pregunta = PREGUNTAS_SEGURIDAD[(int) $datos['pregunta']];
    $hashRespuesta = password_hash(normalizarRespuesta(textoCampo($datos, 'respuesta')), PASSWORD_DEFAULT);
    $asesino = textoCampo($datos, 'asesino');
    $nacimiento = textoCampo($datos, 'fecha_nacimiento');

    try {
        $stmt = conectar()->prepare(
            'INSERT INTO usuarios
                (usuario, password_hash, email, perfil, pregunta, respuesta_hash, asesino, fecha_nacimiento)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'ssssssss',
            $usuario, $hashClave, $email, $perfil, $pregunta, $hashRespuesta, $asesino, $nacimiento
        );
        $stmt->execute();
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        // 1062 = clave duplicada: el usuario o el email ya existen
        if ($e->getCode() === 1062) {
            $campo = str_contains($e->getMessage(), 'email') ? 'email' : 'usuario';

            return ['ok' => false, 'errores' => [$campo => 'Ese ' . $campo . ' ya está registrado.']];
        }
        throw $e;
    }

    return ['ok' => true, 'errores' => []];
}

/**
 * Comprueba usuario y contraseña. Si son correctos, guarda al usuario
 * en la sesión y devuelve sus datos; si no, devuelve null.
 */
function loginUsuario(string $usuario, string $clave): ?array
{
    $usuario = trim($usuario);
    if ($usuario === '' || $clave === '') {
        return null;
    }

    $stmt = conectar()->prepare(
        'SELECT id, usuario, password_hash, email, perfil, asesino FROM usuarios WHERE usuario = ?'
    );
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $stmt->bind_result($id, $nombre, $hash, $email, $perfil, $asesino);
    $existe = $stmt->fetch() === true;
    $stmt->close();

    if (!$existe || !password_verify($clave, $hash)) {
        return null;
    }

    iniciarSesion();
    session_regenerate_id(true); // evita reutilizar un identificador de sesión anterior

    // En la sesión nunca se guarda el hash
    $_SESSION['usuario'] = [
        'id'      => $id,
        'usuario' => $nombre,
        'email'   => $email,
        'perfil'  => $perfil,
        'asesino' => $asesino,
    ];

    return $_SESSION['usuario'];
}

/**
 * Devuelve los datos del usuario logueado o null si no hay sesión.
 */
function usuarioActual(): ?array
{
    iniciarSesion();

    return $_SESSION['usuario'] ?? null;
}

/**
 * Cierra la sesión actual.
 */
function cerrarSesion(): void
{
    iniciarSesion();
    $_SESSION = [];
    session_destroy();
}

/**
 * Devuelve la pregunta de seguridad de un usuario, o null si no existe.
 */
function preguntaDeUsuario(string $usuario): ?string
{
    $stmt = conectar()->prepare('SELECT pregunta FROM usuarios WHERE usuario = ?');
    $usuario = trim($usuario);
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $stmt->bind_result($pregunta);
    $existe = $stmt->fetch() === true;
    $stmt->close();

    return $existe ? $pregunta : null;
}

/**
 * Cambia la contraseña si la respuesta de seguridad es correcta.
 * Devuelve ['ok' => bool, 'error' => string].
 */
function cambioContrasena(string $usuario, string $respuesta, string $nuevaClave): array
{
    if (mb_strlen($nuevaClave) < 8 || mb_strlen($nuevaClave) > 72) {
        return ['ok' => false, 'error' => 'La contraseña debe tener entre 8 y 72 caracteres.'];
    }

    $usuario = trim($usuario);
    $stmt = conectar()->prepare('SELECT respuesta_hash FROM usuarios WHERE usuario = ?');
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $stmt->bind_result($hashRespuesta);
    $existe = $stmt->fetch() === true;
    $stmt->close();

    // Mismo mensaje si falla el usuario o la respuesta
    if (!$existe || !password_verify(normalizarRespuesta($respuesta), $hashRespuesta)) {
        return ['ok' => false, 'error' => 'El usuario o la respuesta no son correctos.'];
    }

    $nuevoHash = password_hash($nuevaClave, PASSWORD_DEFAULT);
    $stmt = conectar()->prepare('UPDATE usuarios SET password_hash = ? WHERE usuario = ?');
    $stmt->bind_param('ss', $nuevoHash, $usuario);
    $stmt->execute();
    $stmt->close();

    return ['ok' => true, 'error' => ''];
}

/**
 * Datos que se muestran en la lápida de un usuario: asesino y fechas.
 * Devuelve null si el usuario no existe.
 */
function datosLapida(string $usuario): ?array
{
    $stmt = conectar()->prepare(
        'SELECT asesino, fecha_nacimiento, DATE(fecha_alta) FROM usuarios WHERE usuario = ?'
    );
    $usuario = trim($usuario);
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $stmt->bind_result($asesino, $nacimiento, $alta);
    $existe = $stmt->fetch() === true;
    $stmt->close();

    return $existe
        ? ['asesino' => $asesino, 'nacimiento' => $nacimiento, 'alta' => $alta]
        : null;
}