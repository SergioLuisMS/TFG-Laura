<?php

namespace App\Enums;

/**
 * Defino aqui los dos estados posibles de una relacion de amistad.
 * Evito tener los strings 'pendiente' y 'aceptada' dispersos por varios controladores.
 */
enum EstadoAmistad: string
{
    case Pendiente = 'pendiente';
    case Aceptada  = 'aceptada';
}
