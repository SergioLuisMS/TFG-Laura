<?php

namespace App\Support;

/**
 * Extraigo aqui la logica de traduccion de generos que antes vivia en LibroController.
 * Asi puedo reutilizarla desde cualquier parte del codigo sin duplicar la logica.
 *
 * Recibe un texto (genero de Google Books + titulo del libro) y lo normaliza
 * a uno de los 9 generos internos del sistema.
 */
class GeneroTraductor
{
    /**
     * Los 9 generos que maneja el sistema internamente.
     * Todo lo que no encaje en los anteriores cae en Narrativa.
     */
    private const GENEROS_VALIDOS = [
        'Romántica',
        'Fantasía',
        'Policiaca',
        'Terror',
        'Ciencia Ficción',
        'Aventura',
        'Historia',
        'Clásicos',
        'Narrativa',
    ];

    /**
     * Mapa de palabras clave por genero.
     * El orden importa: el primer patron que coincide gana.
     * Mantengo los comentarios del codigo original para no perder el razonamiento de cada decision.
     */
    private const MAPA = [
        // Quitamos 'amo' (da falsos positivos con "famous") y 'roman' (machea "Romanian")
        'Romántica' => [
            'amor', 'love', 'romance', 'romantic', 'relat',
            'noviazgo', 'beso', 'erotic', 'passion', 'seduct',
        ],

        // Cambiamos 'epic' solo por 'epic fantasy' para no machucar epicas historicas
        'Fantasía' => [
            'fantas', 'magia', 'wizard', 'witch', 'potter',
            'myth', 'dragones', 'mistborn', 'bruma', 'épica',
            'epic fantasy', 'sword', 'espada', 'sorcer',
        ],

        'Policiaca' => [
            'crimen', 'polic', 'detect', 'mister', 'noir', 'thrill', 'investig',
        ],

        'Terror' => [
            'horror', 'terror', 'miedo', 'ghost', 'suspens', 'paranormal',
        ],

        // Quitamos 'space' porque da falsos positivos con workspace, airspace, etc.
        'Ciencia Ficción' => [
            'science fiction', 'sci-fi', 'robot', 'dystop', 'futurist',
            'cyber', 'estelar', 'galact', 'spaceship', 'starship', 'interstellar', 'alien',
        ],

        'Aventura' => ['aventur', 'adventur', 'action', 'explor'],

        'Historia' => ['histor', 'biogra', 'war', 'guerra'],

        'Clásicos' => ['classic', 'antiqu', 'ancient'],
    ];

    /**
     * Devuelvo true si el texto ya es exactamente uno de nuestros generos internos.
     * Lo uso para no volver a traducir un genero que ya llega procesado desde la vista.
     */
    public static function esValido(string $genero): bool
    {
        return in_array($genero, self::GENEROS_VALIDOS, true);
    }

    /**
     * Convierto cualquier texto libre al genero interno correspondiente.
     * Si ninguna palabra clave coincide, devuelvo 'Narrativa' como valor por defecto.
     */
    public static function traducir(string $texto): string
    {
        $texto = strtolower(trim($texto));

        foreach (self::MAPA as $categoria => $patrones) {
            foreach ($patrones as $patron) {
                if (str_contains($texto, $patron)) {
                    return $categoria;
                }
            }
        }

        return 'Narrativa';
    }

    /**
     * Devuelvo el array completo de generos validos.
     * Util para construir validaciones o mostrar listas en vistas.
     */
    public static function todos(): array
    {
        return self::GENEROS_VALIDOS;
    }
}
