<?php

namespace App\Services;

use App\Support\GeneroTraductor;
use Illuminate\Support\Facades\Http;

/**
 * Centralizo aqui la busqueda de libros en APIs externas.
 *
 * Intento primero Google Books (es la fuente preferida por su catalogo y portadas),
 * pero como las peticiones sin API key comparten una cuota anonima que se agota
 * facilmente (Google devuelve HTTP 429), uso Open Library como respaldo automatico.
 * Open Library es publica, gratuita y no exige clave ni tiene limite practico,
 * asi que garantiza que la busqueda funcione aunque Google este saturado.
 *
 * Devuelvo siempre un formato normalizado para que el controlador y la vista
 * no dependan de la estructura concreta de cada API.
 */
class BuscadorLibrosService
{
    /**
     * Busco libros por texto y devuelvo un resultado normalizado.
     *
     * El array de retorno tiene dos claves:
     *  - 'libros': lista de libros ya normalizados (titulo, autor, portada, genero).
     *  - 'fuente': nombre de la API que respondio, o null si ninguna estuvo disponible.
     *
     * Cada libro normalizado tiene estas claves:
     *  - 'titulo', 'autor', 'portada', 'genero'
     */
    public function buscar(string $query): array
    {
        // 1. Intento Google Books primero (fuente preferida).
        $google = $this->buscarEnGoogleBooks($query);
        if ($google !== null && count($google) > 0) {
            return ['libros' => $google, 'fuente' => 'Google Books'];
        }

        // 2. Si Google falla (429, error de red) o no devuelve nada, uso Open Library.
        $openLibrary = $this->buscarEnOpenLibrary($query);
        if ($openLibrary !== null) {
            return ['libros' => $openLibrary, 'fuente' => 'Open Library'];
        }

        // 3. Si Google fallo y devolvio resultados vacios pero Open Library
        //    tampoco respondio, devuelvo lo que tuviera Google (probablemente vacio).
        if ($google !== null) {
            return ['libros' => $google, 'fuente' => 'Google Books'];
        }

        // 4. Ninguna API respondio: aviso al controlador con fuente null.
        return ['libros' => [], 'fuente' => null];
    }

    /**
     * Consulto Google Books y normalizo la respuesta.
     *
     * Devuelvo null si hay un error de red o la API responde con un codigo
     * que no sea 2xx (por ejemplo 429 por cuota agotada). Devuelvo un array
     * (posiblemente vacio) si la peticion fue correcta.
     */
    private function buscarEnGoogleBooks(string $query): ?array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->get('https://www.googleapis.com/books/v1/volumes', [
                    'q'          => $query,
                    'maxResults' => 20,
                ]);

            // Un 429 (cuota agotada) o cualquier error: devuelvo null para que se use el respaldo.
            if (!$response->successful()) {
                return null;
            }

            $items = $response->json()['items'] ?? [];
            $libros = [];

            foreach ($items as $item) {
                $info = $item['volumeInfo'] ?? [];

                $titulo  = $info['title'] ?? 'Sin titulo';
                $autores = $info['authors'] ?? [];
                $portada = $info['imageLinks']['thumbnail'] ?? null;
                $genero  = $info['categories'][0] ?? '';

                $libros[] = $this->normalizar($titulo, $autores, $portada, $genero);
            }

            return $libros;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Consulto Open Library y normalizo la respuesta.
     *
     * Open Library no necesita clave ni tiene limite practico de llamadas.
     * Devuelvo null solo si hay un error de red o la API no responde con 2xx;
     * en cualquier otro caso devuelvo la lista normalizada (puede estar vacia).
     */
    private function buscarEnOpenLibrary(string $query): ?array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->get('https://openlibrary.org/search.json', [
                    'q'      => $query,
                    'limit'  => 20,
                    // Pido solo los campos que necesito para que la respuesta sea ligera.
                    'fields' => 'title,author_name,cover_i,subject,first_publish_year',
                ]);

            if (!$response->successful()) {
                return null;
            }

            $docs   = $response->json()['docs'] ?? [];
            $libros = [];

            foreach ($docs as $doc) {
                $titulo  = $doc['title'] ?? 'Sin titulo';
                $autores = $doc['author_name'] ?? [];

                // La portada se construye con el id de portada de Open Library, si existe.
                $portada = isset($doc['cover_i'])
                    ? "https://covers.openlibrary.org/b/id/{$doc['cover_i']}-M.jpg"
                    : null;

                // Open Library da los generos en 'subject'; uso los primeros para detectar el genero.
                $subjects = $doc['subject'] ?? [];
                $genero   = implode(' ', array_slice($subjects, 0, 5));

                $libros[] = $this->normalizar($titulo, $autores, $portada, $genero);
            }

            return $libros;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convierto los datos crudos de cualquier API a mi formato interno comun.
     *
     * Aprovecho para traducir el genero al sistema interno combinando el genero
     * crudo con el titulo (mejora la deteccion, igual que hacia el controlador antes).
     * Si no hay portada, uso la imagen local de "sin portada".
     */
    private function normalizar(string $titulo, array $autores, ?string $portada, string $generoCrudo): array
    {
        // Fuerzo https en la portada para evitar contenido mixto cuando se despliegue con https.
        if ($portada) {
            $portada = str_replace('http://', 'https://', $portada);
        }

        return [
            'titulo'  => $titulo,
            'autor'   => count($autores) > 0 ? implode(', ', $autores) : 'Autor desconocido',
            'portada' => $portada ?: asset('img/no-portada.svg'),
            'genero'  => GeneroTraductor::traducir($generoCrudo . ' ' . $titulo),
        ];
    }
}
