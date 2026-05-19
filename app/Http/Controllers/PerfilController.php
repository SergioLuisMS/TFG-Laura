<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Libro;
use App\Models\SesionEstudio;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PerfilController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        // 1. CÁLCULO DEL GÉNERO MÁS VALORADO
        $estadisticasGeneros = Libro::query() // En lugar de Book::query()
            ->join('book_user', 'books.id', '=', 'book_user.book_id')
            ->select('books.genre', \Illuminate\Support\Facades\DB::raw('AVG(book_user.puntuacion) as media_puntuacion'))
            ->where('book_user.user_id', $usuario->id) // Filtramos explícitamente por el usuario
            ->whereNotNull('book_user.puntuacion')
            ->where('book_user.puntuacion', '>', 0)
            ->groupBy('books.genre')
            ->orderBy('media_puntuacion', 'desc')
            ->get();

        // 2. Tiempo de estudio (DESGLOSADO POR SALA)
        // 🎯 Añadimos esta parte para el desglose en el perfil
        $tiemposPorSala = \App\Models\SesionEstudio::where('user_id', $usuario->id)
            ->select('sala', \Illuminate\Support\Facades\DB::raw('SUM(segundos) as total_segundos'))
            ->groupBy('sala')
            ->get();

        // Calculamos el total general (lo que ya tenías)
        $segundosTotales = $tiemposPorSala->sum('total_segundos') ?? 0;
        $minutosTotales = floor($segundosTotales / 60);

        // 3. Enviamos todo a la vista 'perfil'
        return view('perfil', [
            'user' => $usuario,
            'estadisticasGeneros' => $estadisticasGeneros,
            'minutosTotales' => $minutosTotales,
            'tiemposPorSala' => $tiemposPorSala // 👈 ¡ESTA ES LA CLAVE!
        ]);
    }

    public function editarAvatar()
    {
        return view('perfil.editar-avatar');
    }

    public function actualizarAvatar(Request $request)
    {
        // Define aquí exactamente los nombres de tus archivos (sin la ruta, solo el nombre)
        $basesPermitidas = ['base1.png', 'base2.png', 'base3.png'];
        $bocasPermitidas = ['boca1.png', 'boca2.png'];
        $ojosPermitidos  = ['ojos1.png', 'ojos2.png'];
        $compsPermitidos = ['comp1.png', 'comp2.png', 'ninguno.png'];

        $request->validate([
            'avatar_base' => ['required', 'in:' . implode(',', $basesPermitidas)],
            'avatar_boca' => ['required', 'in:' . implode(',', $bocasPermitidas)],
            'avatar_ojos' => ['required', 'in:' . implode(',', $ojosPermitidos)],
            'avatar_complemento' => ['required', 'in:' . implode(',', $compsPermitidos)],
        ]);

        $user = Auth::user();
        $user->update($request->only([
            'avatar_base',
            'avatar_boca',
            'avatar_ojos',
            'avatar_complemento'
        ]));

        return redirect()->route('perfil')->with('success', '¡Avatar actualizado! 🥔✨');
    }

    public function actualizarNombre(Request $request)
    {
        // 1. Validación estricta
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50', // Límite razonable para un nombre
                'min:1',
                // unique:users,name -> asegura que no haya dos personas con el mismo nombre
                // . auth()->id() -> ¡IMPORTANTE! Esto permite que el usuario mantenga su nombre actual
                'unique:users,name,' . auth()->id(),
            ],
        ]);

        // 2. Obtener el usuario y actualizar
        $user = User::find(auth()->id());

        // Al usar ->validated() solo tomamos lo que pasó el filtro
        $user->update([
            'name' => $request->input('name')
        ]);

        // 3. Refrescar la sesión por seguridad
        auth()->setUser($user);

        return back()->with('success', '¡Nombre actualizado correctamente! 🥔✨');
    }

    public function visitarPerfil($id)
    {
        $usuarioActual = Auth::id();

        // 1. Si intenta visitar su propio perfil, lo dejamos pasar
        if ($id == $usuarioActual) {
            return redirect()->route('perfil');
        }

        // 2. Buscamos el usuario visitado
        $amigo = User::findOrFail($id);

        // 3. Comprobamos si son amigos (estado 'aceptada')
        $esAmigo = \App\Models\Amigo::where(function ($q) use ($usuarioActual, $id) {
            $q->where('usuario_id', $usuarioActual)->where('amigo_id', $id);
        })->orWhere(function ($q) use ($usuarioActual, $id) {
            $q->where('usuario_id', $id)->where('amigo_id', $usuarioActual);
        })->where('estado', 'aceptada')->exists();

        // 4. Si NO son amigos, bloqueamos el acceso
        if (!$esAmigo) {
            abort(403, 'No tienes permiso para ver esta estantería. ¡Añádele como amigo primero! 🥔');
        }

        $books = $amigo->libros;

        return view('amigos.perfil-amigo', compact('amigo', 'books'));
    }
}
