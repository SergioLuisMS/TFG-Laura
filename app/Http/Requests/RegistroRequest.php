<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valido aqui todos los datos del formulario de registro.
 * Ademas de los campos basicos, incluyo la validacion de la whitelist de avatares
 * que antes vivia en AuthController. Centralizo las listas de valores permitidos
 * en constantes para que sea facil anadir nuevas opciones en el futuro.
 */
class RegistroRequest extends FormRequest
{
    private const BASES_VALIDAS = [
        'base/azulRelleno.png',
        'base/moradoRelleno.png',
        'base/naranjaRelleno.png',
        'base/rosaRelleno.png',
        'base/verdeRelleno.png',
    ];

    private const BOCAS_VALIDAS = [
        'boca/boca1.png',
        'boca/boca2.png',
        'boca/boca3.png',
        'boca/boca4.png',
    ];

    private const OJOS_VALIDOS = [
        'ojos/ojos1.png',
        'ojos/ojos2.png',
        'ojos/ojos3.png',
    ];

    private const COMPLEMENTOS_VALIDOS = [
        'complemento/complemento1.png',
        'complemento/complemento2.png',
        'complemento/complemento3.png',
        'complemento/complemento4.png',
        'complemento/complemento5.png',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'password'           => 'required|min:6|confirmed',
            'avatar_base'        => ['required', 'in:' . implode(',', self::BASES_VALIDAS)],
            'avatar_boca'        => ['required', 'in:' . implode(',', self::BOCAS_VALIDAS)],
            'avatar_ojos'        => ['required', 'in:' . implode(',', self::OJOS_VALIDOS)],
            'avatar_complemento' => ['required', 'in:' . implode(',', self::COMPLEMENTOS_VALIDOS)],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'          => '¡Esta patata ya tiene dueño! El correo ya está registrado.',
            'password.confirmed'    => 'Las contraseñas no coinciden. ¡Comprueba que las dos son iguales!',
            'required'              => '¡Tu patata no puede nacer incompleta! Elige todas las opciones.',
            'avatar_base.in'        => 'La base del avatar seleccionada no es valida.',
            'avatar_boca.in'        => 'La boca del avatar seleccionada no es valida.',
            'avatar_ojos.in'        => 'Los ojos del avatar seleccionados no son validos.',
            'avatar_complemento.in' => 'El complemento del avatar seleccionado no es valido.',
        ];
    }
}
