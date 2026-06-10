<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valido aqui los datos que llegan al endpoint POST /libros/guardar.
 * Antes esta validacion estaba inline en el controlador; la extraigo
 * para mantener los controladores mas limpios.
 */
class GuardarLibroRequest extends FormRequest
{
    /**
     * Cualquier usuario autenticado puede guardar libros.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validacion para guardar un libro.
     * El genero y la portada son opcionales porque Google Books no siempre los devuelve.
     */
    public function rules(): array
    {
        return [
            'titulo'  => 'required|string|max:500',
            'autor'   => 'required|string|max:255',
            'genero'  => 'nullable|string|max:100',
            'portada' => 'nullable|max:1000',
        ];
    }

    /**
     * Mensajes de error en espanol para mantener la coherencia con el resto del proyecto.
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'El libro necesita un titulo.',
            'autor.required'  => 'El libro necesita un autor.',
        ];
    }
}
