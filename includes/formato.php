<?php
/**
 * Funciones de presentación compartidas por las vistas.
 */

/**
 * Devuelve la edad en texto: "3 años, 2 meses", "1 año", "5 meses" o "Menos de 1 mes".
 */
function formatearEdad(int $anios, int $meses): string
{
    $partes = [];

    if ($anios > 0) {
        $partes[] = $anios . ' ' . ($anios === 1 ? 'año' : 'años');
    }

    if ($meses > 0) {
        $partes[] = $meses . ' ' . ($meses === 1 ? 'mes' : 'meses');
    }

    return $partes ? implode(', ', $partes) : 'Menos de 1 mes';
}

