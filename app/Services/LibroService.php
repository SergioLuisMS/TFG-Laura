<?php

namespace App\Services;

use App\Models\Libro;
use App\Support\GeneroTraductor;
use Illuminate\Support\Facades\DB;

/**
 * Centralizo aqui la logica de negocio relacionada con los libros.
 * Extraigo el codigo que antes vivia dentro de LibroController para que el
 * controlador solo coordine y este servicio decida como se guardan los libros.
 */
class LibroService
{
    /**
     * Guardo un libro en la estanteria del usuario.
     *
     * Si el libro ya existe por titulo y autor, reutilizo el registro sin tocarlo.
     * El genero se guarda solo en la primera creacion; nunca lo sobreescribo
     * para mantener coherencia entre todos los usuarios que comparten ese libro.
     *
     * Si el usuario ya tiene el libro, actualizo su fila en el pivote
     * con estado 'por_leer' y puntuacion 1 (inicio limpio).
     */
    public function guardarEnEstanteria(int $userId, array $datos): Libro
    {
        $generoFinal = GeneroTraductor::esValido($datos['genero'] ?? '')
            ? $datos['genero']
            : GeneroTraductor::traducir($datos['genero'] ?? '');

        $libro = Libro::firstOrCreate(
            ['title' => $datos['titulo'], 'author' => $datos['autor']],
            [
                'cover_url' => $datos['portada'] ?? null,
                'genre'     => $generoFinal,
                'user_id'   => $userId,
            ]
        );

        DB::table('book_user')->updateOrInsert(
            ['user_id' => $userId, 'book_id' => $libro->id],
            [
                'estado'     => 'por_leer',
                'puntuacion' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return $libro;
    }
}
