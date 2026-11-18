<?php
/**
 * Funciones auxiliares de formateo y presentación para las vistas del sistema.
 *
 * @package RegistroMascotas\Includes
 */

/**
 * Convierte los valores enteros de años y meses de una mascota en una etiqueta
 * legible con concordancia singular/plural, omitiendo las unidades en cero.
 *
 * Ejemplos de salida: "3 años, 2 meses", "1 año", "5 meses" o "Menos de 1 mes"
 * cuando tanto años como meses son igual a 0.
 *
 * @param int $anios Años cumplidos de la mascota (rango válido: 0 a 100).
 * @param int $meses Meses adicionales de la mascota (rango válido: 0 a 11).
 * @return string Cadena formateada lista para mostrarse en tablas y tarjetas.
 */
function formatearEdad(int $anios, int $meses): string
{
    $partes = [];

    /* Evalúa años mayores a cero y aplica flexión singular ('año') o plural ('años') */
    if ($anios > 0) {
        $partes[] = $anios . ' ' . ($anios === 1 ? 'año' : 'años');
    }

    /* Evalúa meses mayores a cero y aplica flexión singular ('mes') o plural ('meses') */
    if ($meses > 0) {
        $partes[] = $meses . ' ' . ($meses === 1 ? 'mes' : 'meses');
    }

    /* Une los componentes existentes con coma o devuelve el texto para recién nacidos */
    return $partes ? implode(', ', $partes) : 'Menos de 1 mes';
}

