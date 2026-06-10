<?php

namespace App\Http\Controllers;

use App\Enums\EstadoAmistad;
use App\Models\Amigo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AmigoController extends Controller
{
    /**
     * Muestro las tres pestanas de amistades: descubrir, mis amigos y solicitudes recibidas.
     *
     * Uso paginacion en la lista de "nuevas patatas" para no cargar todos los usuarios
     * de golpe cuando la base de datos crezca (Mejora #22).
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        // Obtengo todas las relaciones donde participo (aceptadas o pendientes)
        $todasMisRelaciones = Amigo::where('usuario_id', $userId)
            ->orWhere('amigo_id', $userId)
            ->get();

        // IDs de todos los usuarios con los que ya tengo alguna relacion
        $relacionesIds = $todasMisRelaciones->flatMap(function ($rel) {
            return [$rel->usuario_id, $rel->amigo_id];
        })->unique()->toArray();

        // Usuarios sin ninguna relacion conmigo (pestaña "Descubrir")
        $usuarios = User::where('id', '!=', $userId)
            ->whereNotIn('id', $relacionesIds)
            ->withCount('libros')
            ->paginate(12);

        // IDs de mis amigos aceptados en cualquier direccion
        $misRelacionesIds = $todasMisRelaciones->filter(function ($rel) {
            return $rel->estado === EstadoAmistad::Aceptada->value;
        })->map(function ($rel) use ($userId) {
            return $rel->usuario_id == $userId ? $rel->amigo_id : $rel->usuario_id;
        })->unique();

        $misAmigos = User::whereIn('id', $misRelacionesIds)
            ->withCount('libros')
            ->get();

        // Solicitudes que he recibido y aun no he respondido
        $solicitudesRecibidas = Amigo::where('amigo_id', $userId)
            ->where('estado', EstadoAmistad::Pendiente->value)
            ->with('sender')
            ->get();

        $solicitudesPendientes = $solicitudesRecibidas->count();

        return view('usuarios', compact(
            'usuarios',
            'misAmigos',
            'solicitudesPendientes',
            'solicitudesRecibidas'
        ));
    }

    /**
     * Envio una solicitud de amistad al usuario con el id indicado.
     *
     * Compruebo las dos direcciones de la tabla para evitar duplicados
     * tanto si yo ya envie una solicitud antes como si el otro me la envio a mi (Bug #4).
     */
    public function enviarSolicitud($id)
    {
        $userId  = auth()->id();
        $amigoId = (int) $id;

        if ($userId === $amigoId) {
            return back()->with('error', 'No puedes enviarte una solicitud a ti mismo.');
        }

        $existe = Amigo::where(function ($q) use ($userId, $amigoId) {
            $q->where('usuario_id', $userId)->where('amigo_id', $amigoId);
        })->orWhere(function ($q) use ($userId, $amigoId) {
            $q->where('usuario_id', $amigoId)->where('amigo_id', $userId);
        })->exists();

        if ($existe) {
            return back()->with('error', 'Ya existe una relacion con este usuario.');
        }

        Amigo::create([
            'usuario_id' => $userId,
            'amigo_id'   => $amigoId,
            'estado'     => EstadoAmistad::Pendiente->value,
        ]);

        return back()->with('success', 'Solicitud enviada.');
    }

    /**
     * Acepto la solicitud de amistad enviada por el usuario con el id indicado.
     * Solo puedo aceptar solicitudes que me hayan enviado a mi, no las que yo envie.
     */
    public function aceptarSolicitud($id)
    {
        $userId    = auth()->id();
        $solicitud = Amigo::where('usuario_id', $id)
            ->where('amigo_id', $userId)
            ->where('estado', EstadoAmistad::Pendiente->value)
            ->first();

        if (!$solicitud) {
            return back()->with('error', 'No se encontro la solicitud.');
        }

        $solicitud->update(['estado' => EstadoAmistad::Aceptada->value]);

        return redirect()->route('amigos.index', ['tab' => 'mis-amigos'])
            ->with('success', 'Nueva amistad aceptada.');
    }

    /**
     * Rechazo la solicitud de amistad. Elimino el registro para no dejar rastro.
     * Solo puedo rechazar solicitudes que me hayan enviado a mi.
     */
    public function rechazarSolicitud($id)
    {
        $userId    = auth()->id();
        $solicitud = Amigo::where('usuario_id', $id)
            ->where('amigo_id', $userId)
            ->where('estado', EstadoAmistad::Pendiente->value)
            ->first();

        if (!$solicitud) {
            return back()->with('error', 'No se encontro la solicitud.');
        }

        $solicitud->delete();

        return back()->with('success', 'Solicitud rechazada.');
    }

    /**
     * Elimino una amistad existente en cualquier direccion.
     */
    public function eliminarAmigo($id)
    {
        $userId  = auth()->id();
        $relacion = Amigo::where(function ($q) use ($userId, $id) {
            $q->where('usuario_id', $userId)->where('amigo_id', $id);
        })->orWhere(function ($q) use ($userId, $id) {
            $q->where('usuario_id', $id)->where('amigo_id', $userId);
        })->first();

        if (!$relacion) {
            return back()->with('error', 'No se encontro la relacion.');
        }

        $relacion->delete();

        return back()->with('success', 'Amistad eliminada.');
    }
}
