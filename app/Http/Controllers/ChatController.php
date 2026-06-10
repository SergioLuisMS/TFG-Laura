<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Las mismas salas válidas que en SalaController, centralizadas aquí
    private const SALAS_VALIDAS = ['botica', 'biblioteca', 'despacho-rosa', 'dormitorio', 'despacho-neutro', 'jardin'];

    // Guardar un mensaje en la BD
    public function enviar(Request $request)
    {
        $request->validate([
            'sala'    => 'required|string|in:' . implode(',', self::SALAS_VALIDAS),
            'mensaje' => 'required|string|max:500',
        ]);

        $id = DB::table('chat_mensajes')->insertGetId([
            'user_id'    => Auth::id(),
            'sala'       => $request->sala,
            'nombre'     => Auth::user()->name,
            'mensaje'    => $request->mensaje,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    // Obtener mensajes nuevos a partir de un ID concreto (para el polling del cliente)
    public function obtener(Request $request)
    {
        $request->validate([
            'sala'  => 'required|string|in:' . implode(',', self::SALAS_VALIDAS),
            'desde' => 'integer|min:0',
        ]);

        $desde = $request->integer('desde', 0);

        $mensajes = DB::table('chat_mensajes')
            ->where('sala', $request->sala)
            ->where('id', '>', $desde)
            ->orderBy('id')
            ->limit(50)
            ->get(['id', 'nombre', 'mensaje']);

        return response()->json(['mensajes' => $mensajes]);
    }
}
