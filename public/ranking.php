<?php
declare(strict_types=1);

/**
 * Ranking de asesinos.
 * Lee data/personajes.json, ordena por muertes (de más a menos) y pinta una fila por personaje.
 * El orden del JSON no importa: el puesto se calcula aquí. En caso de empate se respeta el orden del archivo.
 */

// Ajusta esta ruta si la página principal es otra (por ejemplo index.html).
const URL_PRINCIPAL = 'principal.php';
const RUTA_DATOS = __DIR__ . '/data/personajes.json';
const RUTA_IMAGENES = 'assets/img/personajes/';

function escapar(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

/**
 * Devuelve los personajes válidos del JSON, ordenados por muertes descendente.
 * Las entradas sin nombre o con muertes no numéricas se descartan.
 */
function cargarPersonajes(string $ruta): array
{
    $json = @file_get_contents($ruta);
    if ($json === false) {
        return [];
    }

    $datos = json_decode($json, true);
    if (!is_array($datos)) {
        return [];
    }

    $personajes = [];
    foreach ($datos as $dato) {
        if (!is_array($dato) || !isset($dato['nombre'], $dato['muertes'])) {
            continue;
        }
        if (!is_string($dato['nombre']) || !is_numeric($dato['muertes'])) {
            continue;
        }

        $personajes[] = [
            'nombre'  => trim($dato['nombre']),
            'muertes' => max(0, (int) $dato['muertes']),
            'imagen'  => isset($dato['imagen']) && is_string($dato['imagen']) ? basename($dato['imagen']) : '',
            'obra'    => isset($dato['obra']) && is_string($dato['obra']) ? trim($dato['obra']) : '',
            'descripcion' => isset($dato['descripcion']) && is_string($dato['descripcion']) ? trim($dato['descripcion']) : '',
        ];
    }

    // usort es estable desde PHP 8.0: los empates conservan el orden del JSON.
    usort($personajes, fn (array $a, array $b): int => $b['muertes'] <=> $a['muertes']);

    return $personajes;
}

$personajes = cargarPersonajes(RUTA_DATOS);
$maximo = $personajes ? max(1, $personajes[0]['muertes']) : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ranking de asesinos · Halloween Atlántida</title>
    <script>document.documentElement.classList.add('js');</script>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/ranking.css">
</head>
<body class="ranking-pagina">
    <main class="ranking">
        <header class="ranking__cabecera">
            <a class="ranking__volver" href="<?= escapar(URL_PRINCIPAL) ?>">
                <span aria-hidden="true">←</span> Volver a la página principal
            </a>
            <h1 class="ranking__titulo">Ranking de asesinos</h1>
        </header>

<?php if (!$personajes): ?>
        <p class="ranking__vacio">No se ha podido cargar el ranking. Comprueba que existe <code>data/personajes.json</code> y que su formato es correcto.</p>
<?php else: ?>
    <p class="ranking__ayuda">
            Pulsa la foto del asesino que quieras para ver su ficha. Desde ahí podrás
            cambiar el arma que te acompaña junto al cursor (solo en ordenador).
    </p>
        <ol class="ranking__lista" id="ranking-lista">
<?php foreach ($personajes as $indice => $personaje): ?>
<?php
    $puesto = $indice + 1;
    $porcentaje = round($personaje['muertes'] / $maximo * 100, 2);
    $clases = 'fila' . ($puesto <= 3 ? ' fila--podio' : '');
    $etiqueta = $personaje['muertes'] === 1 ? 'asesinato' : 'asesinatos';
?>
            <li class="<?= $clases ?>" style="--pct: <?= $porcentaje ?>%; --i: <?= $indice ?>;">
                <span class="fila__puesto"><?= $puesto ?></span>
                <button class="fila__foto" type="button"
                        aria-label="Ver ficha de <?= escapar($personaje['nombre']) ?>"
                        data-obra="<?= escapar($personaje['obra']) ?>"
                        data-descripcion="<?= escapar($personaje['descripcion']) ?>">
<?php if ($personaje['imagen'] !== ''): ?>
                    <img src="<?= escapar(RUTA_IMAGENES . $personaje['imagen']) ?>" alt="" width="56" height="56" loading="lazy" decoding="async">
<?php else: ?>
                    <span class="fila__inicial"><?= escapar(mb_strtoupper(mb_substr($personaje['nombre'], 0, 1))) ?></span>
<?php endif; ?>
                </button>
                <span class="fila__nombre"><?= escapar($personaje['nombre']) ?></span>
                <span class="fila__barra" aria-hidden="true"><span class="fila__relleno"></span></span>
                <span class="fila__muertes"><strong><?= $personaje['muertes'] ?></strong> <?= $etiqueta ?></span>
            </li>
<?php endforeach; ?>
        </ol>

        <dialog class="ficha" id="ficha" aria-labelledby="ficha-nombre">
            <div class="ficha__contenido">
                <button class="ficha__cerrar" id="ficha-cerrar" type="button" aria-label="Cerrar ficha">×</button>

                <div class="ficha__foto">
                    <img id="ficha-img" src="" alt="" width="144" height="144">
                    <span class="ficha__inicial" id="ficha-inicial" hidden></span>
                </div>

                <h2 class="ficha__nombre" id="ficha-nombre"></h2>
                <p class="ficha__obra" id="ficha-obra"></p>
                <p class="ficha__descripcion" id="ficha-descripcion"></p>

                <button class="ficha__arma" type="button">
                    Cambiar arma de asesino
                </button>
            </div>
        </dialog>
<?php endif; ?>
    </main>

    <script src="assets/js/ranking.js" defer></script>
</body>
</html>