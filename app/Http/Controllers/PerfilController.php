<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use App\Models\SesionEstudio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerfilController extends Controller
{
    /**
     * Muestro el perfil del usuario con estadisticas de generos y tiempo por sala.
     */
    public function index()
    {
        $usuario = auth()->user();

        // Calculo el genero mejor puntuado por el usuario
        $estadisticasGeneros = Libro::query()
            ->join('book_user', 'books.id', '=', 'book_user.book_id')
            ->select('books.genre', DB::raw('AVG(book_user.puntuacion) as media_puntuacion'))
            ->where('book_user.user_id', $usuario->id)
            ->whereNotNull('book_user.puntuacion')
            ->where('book_user.puntuacion', '>', 0)
            ->groupBy('books.genre')
            ->orderBy('media_puntuacion', 'desc')
            ->get();

        // Calculo el tiempo de estudio desglosado por sala para mostrarlo en el perfil
        $tiemposPorSala = SesionEstudio::where('user_id', $usuario->id)
            ->select('sala', DB::raw('SUM(segundos) as total_segundos'))
            ->groupBy('sala')
            ->get();

        $segundosTotales = $tiemposPorSala->sum('total_segundos') ?? 0;
        $minutosTotales  = floor($segundosTotales / 60);

        return view('perfil', [
            'user'                => $usuario,
            'estadisticasGeneros' => $estadisticasGeneros,
            'minutosTotales'      => $minutosTotales,
            'tiemposPorSala'      => $tiemposPorSala,
        ]);
    }

    /**
     * Muestro el formulario de personalizacion del avatar.
     */
    public function editarAvatar()
    {
        return view('perfil.editar-avatar');
    }

    /**
     * Guardo los cambios del avatar tras validar que todos los valores estan en la whitelist.
     * La validacion de whitelist protege contra rutas de imagen arbitrarias en la BD.
     */
    public function actualizarAvatar(Request $request)
    {
        $basesPermitidas = [
            'base/azulRelleno.png', 'base/moradoRelleno.png', 'base/naranjaRelleno.png',
            'base/rosaRelleno.png', 'base/verdeRelleno.png',
        ];
        $bocasPermitidas = [
            'boca/boca1.png', 'boca/boca2.png', 'boca/boca3.png', 'boca/boca4.png',
        ];
        $ojosPermitidos = [
            'ojos/ojos1.png', 'ojos/ojos2.png', 'ojos/ojos3.png',
        ];
        $compsPermitidos = [
            'complemento/complemento1.png', 'complemento/complemento2.png',
            'complemento/complemento3.png', 'complemento/complemento4.png',
            'complemento/complemento5.png',
        ];

        $request->validate([
            'avatar_base'        => ['required', 'in:' . implode(',', $basesPermitidas)],
            'avatar_boca'        => ['required', 'in:' . implode(',', $bocasPermitidas)],
            'avatar_ojos'        => ['required', 'in:' . implode(',', $ojosPermitidos)],
            'avatar_complemento' => ['required', 'in:' . implode(',', $compsPermitidos)],
        ]);

        Auth::user()->update($request->only([
            'avatar_base', 'avatar_boca', 'avatar_ojos', 'avatar_complemento',
        ]));

        return redirect()->route('perfil')->with('success', 'Avatar actualizado.');
    }

    /**
     * Actualizo el nombre del usuario asegurandome de que no exista ya en la BD.
     * Excluyo el propio id del usuario para que pueda mantener su nombre actual.
     */
    public function actualizarNombre(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                'min:1',
                'unique:users,name,' . auth()->id(),
            ],
        ]);

        $user = User::find(auth()->id());
        $user->update(['name' => $request->input('name')]);
        auth()->setUser($user);

        return back()->with('success', 'Nombre actualizado correctamente.');
    }

    /**
     * Muestro el perfil de un usuario amigo.
     *
     * Uso la UserPolicy para centralizar la logica de acceso en lugar de
     * tener la comprobacion inline (Seguridad #26).
     * Si no son amigos, la policy lanza un 403 automaticamente.
     */
    public function visitarPerfil($id)
    {
        $amigo = User::findOrFail($id);

        // La policy redirige al propio perfil si el id es el del usuario actual,
        // y lanza 403 si no existe amistad aceptada
        $this->authorize('visitarPerfil', $amigo);

        if ($amigo->id === auth()->id()) {
            return redirect()->route('perfil');
        }

        $books = $amigo->libros()->withPivot('estado', 'puntuacion')->get();

        return view('amigos.perfil-amigo', compact('amigo', 'books'));
    }
}
