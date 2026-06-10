<?php

namespace App\Enums;

/**
 * Defino aqui todos los estados validos de un libro en la estanteria.
 * Centralizo los literales para no repetir strings magicos por todo el codigo.
 */
enum EstadoLibro: string
{
    case PorLeer = 'por_leer';
    case Leyendo = 'leyendo';
    case Leido   = 'leido';

    /**
     * Devuelvo un array plano con todos los valores posibles.
     * Lo uso en las validaciones de los Form Requests.
     */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
