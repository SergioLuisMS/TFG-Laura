<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarEstanteriaRequest;
use App\Http\Requests\GuardarLibroRequest;
use App\Models\Libro;
use App\Services\BuscadorLibrosService;
use App\Services\LibroService;
use Illuminate\Http\Request;

class LibroController extends Controller
{
    /**
     * Inyecto los servicios para que la logica de negocio no viva en el controlador.
     * LibroService guarda libros; BuscadorLibrosService busca en las APIs externas.
     */
    public function __construct(
        private LibroService $libroService,
        private BuscadorLibrosService $buscadorLibros,
    ) {}

    /**
     * Muestro el formulario de busqueda de libros.
     */
    public function inicio()
    {
        return view('libros.index');
    }

    /**
     * Busco libros en APIs externas (Google Books con respaldo en Open Library)
     * y traduzco sus generos al sistema interno.
     *
     * Delego toda la logica de las APIs en BuscadorLibrosService: el controlador
     * solo coordina. Si ninguna API responde, muestro un aviso al usuario para
     * que sepa que el problema es externo (antes este fallo era silencioso, Bug #6).
     */
    public function buscar(Request $request)
    {
        $query = $request->input('query');
        if (!$query) {
            return view('libros.resultados', ['libros' => []]);
        }

        $resultado = $this->buscadorLibros->buscar($query);

        // Si ninguna de las dos APIs estuvo disponible, aviso al usuario.
        if ($resultado['fuente'] === null) {
            return view('libros.resultados', ['libros' => []])
                ->with('warning', 'El servicio de busqueda de libros no esta disponible ahora mismo. Prueba mas tarde.');
        }

        return view('libros.resultados', ['libros' => $resultado['libros']]);
    }

    /**
     * Guardo un libro en la estanteria del usuario via AJAX.
     *
     * Delego la logica de creacion/actualizacion en LibroService para
     * no mezclar reglas de negocio con codigo HTTP aqui.
     */
    public function guardar(GuardarLibroRequest $request)
    {
        try {
            $libro = $this->libroService->guardarEnEstanteria(
                auth()->id(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => "Libro guardado en {$libro->genre}.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar el libro. Intentalo de nuevo.',
            ], 500);
        }
    }

    /**
     * Muestro la estanteria personal del usuario con paginacion.
     *
     * Uso paginacion de 12 libros por pagina para evitar cargar cientos
     * de registros de golpe (Mejora #22).
     */
    public function miEstanteria()
    {
        $books = auth()->user()
            ->libros()
            ->withPivot('estado', 'puntuacion')
            ->paginate(12);

        return view('libros.estanteria', compact('books'));
    }

    /**
     * Filtro los libros de la estanteria por genero y devuelvo HTML parcial via AJAX.
     * El filtro no usa paginacion porque ya esta acotado por genero.
     */
    public function filtrar(Request $request)
    {
        $genero = $request->get('genero');
        $user   = auth()->user();
        $query  = $user->libros();

        if ($genero !== 'todos' && !empty($genero)) {
            $query->where('genre', $genero);
        }

        $books = $query->withPivot('estado', 'puntuacion')->get();
        $html  = view('libros.lista-libros-estanteria', compact('books'))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Actualizo el estado y la puntuacion de un libro en la estanteria del usuario.
     *
     * Solo toco la tabla pivote (book_user); el genero del libro compartido no se modifica.
     * Valido el rango de puntuacion (1-5) en el Form Request para cerrar el Bug #5.
     */
    public function actualizarEstanteria(ActualizarEstanteriaRequest $request, Libro $libro)
    {
        auth()->user()->libros()->updateExistingPivot($libro->id, [
            'estado'     => $request->validated('estado'),
            'puntuacion' => $request->validated('puntuacion'),
        ]);

        return redirect()->back()->with('success', 'Libro actualizado.');
    }

    /**
     * Elimino un libro de la estanteria del usuario (no borro el libro de la BD).
     * Solo corto la relacion en la tabla pivote con detach().
     */
    public function eliminar(Libro $libro)
    {
        auth()->user()->libros()->detach($libro->id);

        return redirect()->back()->with('success', 'Libro quitado de tu estanteria.');
    }
}
