<?php

namespace App\Policies;

use App\Models\Amigo;
use App\Models\User;
use App\Enums\EstadoAmistad;

/**
 * Centralizo aqui las reglas de autorizacion sobre el modelo User.
 * Antes la comprobacion de amistad estaba inline en PerfilController;
 * la muevo a una Policy para que pueda reutilizarse desde otros sitios
 * y para que el controlador no mezcle logica de negocio con logica de acceso.
 */
class UserPolicy
{
    /**
     * Determino si el usuario actual puede visitar el perfil de otro usuario.
     *
     * Las reglas son:
     * - Un usuario siempre puede ver su propio perfil.
     * - Para ver el perfil de otro, la amistad debe estar en estado 'aceptada'
     *   en cualquiera de las dos direcciones de la tabla amigos.
     */
    public function visitarPerfil(User $usuarioActual, User $perfilObjetivo): bool
    {
        if ($usuarioActual->id === $perfilObjetivo->id) {
            return true;
        }

        return Amigo::where(function ($q) use ($usuarioActual, $perfilObjetivo) {
            $q->where('usuario_id', $usuarioActual->id)
              ->where('amigo_id', $perfilObjetivo->id);
        })->orWhere(function ($q) use ($usuarioActual, $perfilObjetivo) {
            $q->where('usuario_id', $perfilObjetivo->id)
              ->where('amigo_id', $usuarioActual->id);
        })->where('estado', EstadoAmistad::Aceptada->value)->exists();
    }
}
