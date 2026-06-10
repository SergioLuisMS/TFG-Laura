<?php

namespace Database\Factories;

use App\Models\Libro;
use App\Models\User;
use App\Support\GeneroTraductor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Libro.
 * Uso GeneroTraductor::todos() para generar generos validos del sistema
 * en lugar de strings aleatorios que romperien los tests de filtrado.
 */
class LibroFactory extends Factory
{
    protected $model = Libro::class;

    public function definition(): array
    {
        $generos = GeneroTraductor::todos();

        return [
            'title'     => fake()->sentence(4),
            'author'    => fake()->name(),
            'genre'     => $generos[array_rand($generos)],
            'cover_url' => 'https://via.placeholder.com/128x192',
            'user_id'   => User::factory(),
        ];
    }
}
