<?php

namespace App\Http\Requests;

use App\Enums\EstadoLibro;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valido aqui los datos del formulario PUT /mi-estanteria/{libro}.
 * Esta validacion faltaba en el controlador original, lo que permitia
 * que llegasen estados o puntuaciones fuera de rango desde peticiones manuales.
 */
class ActualizarEstanteriaRequest extends FormRequest
{
    /**
     * Solo el propio usuario puede actualizar su estanteria.
     * La autorizacion de que el libro pertenece al usuario se comprueba en el controlador.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * El estado debe ser uno de los tres valores del enum EstadoLibro.
     * La puntuacion debe estar entre 1 y 5 (las 5 patatas).
     */
    public function rules(): array
    {
        return [
            'estado'     => ['required', 'string', 'in:' . implode(',', EstadoLibro::valores())],
            'puntuacion' => 'required|integer|between:1,5',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'estado.in'          => 'El estado elegido no es valido.',
            'puntuacion.between' => 'La puntuacion debe estar entre 1 y 5 patatas.',
            'puntuacion.integer' => 'La puntuacion debe ser un numero entero.',
        ];
    }
}
