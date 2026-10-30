<?php

/**
 * Lee el CSV y devuelve una lista de recetas.
 * Cada receta utiliza los nombres de columna como claves.
 */
function cargarRecetasCSV($ruta)
{
    if (!is_readable($ruta)) {
        throw new RuntimeException('No se puede leer el CSV de recetas.');
    }

    $archivo = fopen($ruta, 'r');

    if ($archivo === false) {
        throw new RuntimeException('No se ha podido abrir el CSV de recetas.');
    }

    try {
        $cabeceras = fgetcsv($archivo, 0, ',', '"', '');

        if ($cabeceras === false || $cabeceras === [null]) {
            throw new RuntimeException('El CSV no tiene cabecera.');
        }

        // Elimina una posible marca BOM de UTF-8 al inicio del archivo.
        $cabeceras[0] = preg_replace('/^\xEF\xBB\xBF/', '', $cabeceras[0]);

        $columnasEsperadas = [
            'nombre',
            'foto',
            'tiempo',
            'dificultad',
            'ingredientes',
            'preparación',
            'personas',
        ];

        if ($cabeceras !== $columnasEsperadas) {
            throw new RuntimeException('Las columnas del CSV no son las esperadas.');
        }

        $recetas = [];
        $numeroRegistro = 1;

        while (($fila = fgetcsv($archivo, 0, ',', '"', '')) !== false) {
            $numeroRegistro++;

            // Una línea vacía no representa una receta.
            if ($fila === [null]) {
                continue;
            }

            if (count($fila) !== count($cabeceras)) {
                throw new RuntimeException(
                    'Número de campos incorrecto en el registro ' . $numeroRegistro
                );
            }

            $recetas[] = array_combine($cabeceras, $fila);
        }

        return $recetas;
    } finally {
        fclose($archivo);
    }
}