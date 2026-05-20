<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SesionEstudio;
use Illuminate\Support\Facades\Auth;

class SalaController extends Controller
{
    // Ver el menú de selección de salas (el mapa)
    public function index()
    {
        return view('salas.index');
    }

    // Entrar en una sala específica usando la plantilla única
    public function show($tipo)
    {
        // 🎯 CONFIGURACIÓN MAESTRA DE SALAS
        // Aquí definimos qué cambia en cada una
        $configuracionSalas = [
            'botica' => [
                'titulo' => 'Botica 🧙🏼‍♂️',
                'subtitulo' => 'Silencio... las patatas están destilando sabiduría.',
                'clase' => 'sala-botica',
                'color_borde' => '#34d399', // Verde esmeralda
            ],
            'biblioteca' => [
                'titulo' => 'Biblioteca Antigua 📚',
                'subtitulo' => 'Shhh... las patatas están sumergidas en letras.',
                'clase' => 'sala-biblioteca',
                'color_borde' => '#b87333', // Bronce
            ],
            'despacho-rosa' => [
                'titulo' => 'Despacho Rosa 🌸',
                'subtitulo' => 'Productividad patatil en tonos pastel.',
                'clase' => 'sala-despacho-rosa',
                'color_borde' => '#fce7f3', // Rosa claro
            ],
            'dormitorio' => [
                'titulo' => 'Dormitorio Relax 🛏️',
                'subtitulo' => 'Un rincón tranquilo para estudiar sin prisas.',
                'clase' => 'sala-dormitorio',
                'color_borde' => '#93c5fd', // Azul suave
            ],
            'despacho-neutro' => [
                'titulo' => 'Despacho Minimalista 🖥️',
                'subtitulo' => 'Cero distracciones, máxima concentración.',
                'clase' => 'sala-despacho-neutro',
                'color_borde' => '#d1d5db', // Gris neutro
            ],
            'jardin' => [
                'titulo' => 'Jardín Zen 🌿',
                'subtitulo' => 'Respira hondo... la naturaleza ayuda a concentrarse.',
                'clase' => 'sala-jardin',
                'color_borde' => '#84cc16', // Un verde lima natural
            ],

        ];

        // Verificamos que la sala exista en nuestro array
        if (!array_key_exists($tipo, $configuracionSalas)) {
            abort(404);
        }

        $sala = $configuracionSalas[$tipo];

        // Retornamos la vista única 'salas.sala' pasándole los datos
        return view('salas.sala', compact('tipo', 'sala'));
    }

    // Guardar sesión (Botón Terminar Sesión)
    public function guardar(Request $request)
    {
        $request->validate([
            'segundos' => 'required|integer',
            'sala'     => 'required|string'
        ]);

        $salaLimpia = strtolower(trim($request->sala));
        $userId = Auth::id();
        $hoy = now()->toDateString();

        // Buscamos si ya empezó una sesión hoy en esta sala
        $registro = SesionEstudio::where('user_id', $userId)
            ->where('sala', $salaLimpia)
            ->whereDate('fecha_inicio', $hoy)
            ->first();

        if ($registro) {
            $registro->increment('segundos', $request->segundos);
        } else {
            SesionEstudio::create([
                'user_id'      => $userId,
                'sala'         => $salaLimpia,
                'fecha_inicio' => now(),
                'segundos'     => $request->segundos,
            ]);
        }

        return redirect()->route('salas.index')->with('success', '¡Sesión guardada y tiempo acumulado!');
    }

    // Registrar pulso (Cada 30 segundos vía AJAX)
    public function registrarPulso(Request $request)
    {
        $hoy = now()->toDateString();
        $userId = Auth::id();
        $sala = $request->input('sala');

        $registro = SesionEstudio::where('user_id', $userId)
            ->where('sala', $sala)
            ->whereDate('fecha_inicio', $hoy)
            ->first();

        if ($registro) {
            $registro->increment('segundos', 30);
        } else {
            SesionEstudio::create([
                'user_id'      => $userId,
                'sala'         => $sala,
                'fecha_inicio' => now(),
                'segundos'     => 30
            ]);
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
