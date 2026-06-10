<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valido aqui los datos que llegan al endpoint POST /salas/guardar.
 *
 * El tiempo lo envia el cliente, asi que necesito un techo maximo para evitar
 * que alguien manipule la peticion y registre miles de horas de golpe.
 * Fijo el limite en 86400 segundos (24 horas), que es el maximo razonable en un dia.
 */
class GuardarSesionRequest extends FormRequest
{
    public const MAX_SEGUNDOS  = 86400;
    public const SALAS_VALIDAS = [
        'botica',
        'biblioteca',
        'despacho-rosa',
        'dormitorio',
        'despacho-neutro',
        'jardin',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * El campo 'sala' debe ser uno de los nombres registrados en SalaController.
     * El campo 'segundos' no puede ser negativo ni superar el maximo diario.
     */
    public function rules(): array
    {
        return [
            'sala'     => ['required', 'string', 'in:' . implode(',', self::SALAS_VALIDAS)],
            'segundos' => ['required', 'integer', 'min:0', 'max:' . self::MAX_SEGUNDOS],
        ];
    }

    public function messages(): array
    {
        return [
            'sala.in'          => 'La sala indicada no existe.',
            'segundos.max'     => 'El tiempo registrado supera el maximo permitido por dia.',
            'segundos.min'     => 'El tiempo no puede ser negativo.',
            'segundos.integer' => 'El tiempo debe ser un numero entero de segundos.',
        ];
    }
}
