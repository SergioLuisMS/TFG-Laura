<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarSesionRequest;
use App\Services\SalaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaController extends Controller
{
    /**
     * Inyecto SalaService para que la logica de sesiones viva ahi,
     * no en el controlador.
     */
    public function __construct(private SalaService $salaService) {}

    /**
     * Muestro el mapa de seleccion de salas.
     */
    public function index()
    {
        return view('salas.index');
    }

    /**
     * Muestro una sala especifica con su configuracion visual.
     * Devuelvo 404 si la sala no existe en el mapa de configuracion.
     */
    public function show($tipo)
    {
        $configuracionSalas = [
            'botica' => [
                'titulo'       => 'Botica',
                'subtitulo'    => 'Silencio... las patatas estan destilando sabiduria.',
                'clase'        => 'sala-botica',
                'color_borde'  => '#34d399',
            ],
            'biblioteca' => [
                'titulo'       => 'Biblioteca Antigua',
                'subtitulo'    => 'Shhh... las patatas estan sumergidas en letras.',
                'clase'        => 'sala-biblioteca',
                'color_borde'  => '#b87333',
            ],
            'despacho-rosa' => [
                'titulo'       => 'Despacho Rosa',
                'subtitulo'    => 'Productividad patatil en tonos pastel.',
                'clase'        => 'sala-despacho-rosa',
                'color_borde'  => '#fce7f3',
            ],
            'dormitorio' => [
                'titulo'       => 'Dormitorio Relax',
                'subtitulo'    => 'Un rincon tranquilo para estudiar sin prisas.',
                'clase'        => 'sala-dormitorio',
                'color_borde'  => '#93c5fd',
            ],
            'despacho-neutro' => [
                'titulo'       => 'Despacho Minimalista',
                'subtitulo'    => 'Cero distracciones, maxima concentracion.',
                'clase'        => 'sala-despacho-neutro',
                'color_borde'  => '#d1d5db',
            ],
            'jardin' => [
                'titulo'       => 'Jardin Zen',
                'subtitulo'    => 'Respira hondo... la naturaleza ayuda a concentrarse.',
                'clase'        => 'sala-jardin',
                'color_borde'  => '#84cc16',
            ],
        ];

        if (!array_key_exists($tipo, $configuracionSalas)) {
            abort(404);
        }

        $sala = $configuracionSalas[$tipo];

        return view('salas.sala', compact('tipo', 'sala'));
    }

    /**
     * Guardo la sesion de estudio al pulsar el boton "Terminar".
     *
     * Delego en SalaService que aplica el limite de 86400 segundos (Bug #2 y Seguridad #25).
     * El tiempo del cliente es la fuente de verdad y sobreescribe los pulsos acumulados.
     */
    public function guardar(GuardarSesionRequest $request)
    {
        $this->salaService->guardarSesion(
            Auth::id(),
            $request->validated('sala'),
            $request->validated('segundos')
        );

        return redirect()->route('salas.index')->with('success', 'Sesion guardada.');
    }

    /**
     * Registro un pulso de actividad automatico (llega cada 60s desde el cliente).
     *
     * Este endpoint es el respaldo para cuando el usuario cierra la pagina sin "Terminar".
     * Valido que la sala sea valida usando la misma lista de GuardarSesionRequest.
     */
    public function registrarPulso(Request $request)
    {
        $request->validate([
            'sala' => ['required', 'string', 'in:' . implode(',', GuardarSesionRequest::SALAS_VALIDAS)],
        ]);

        $this->salaService->registrarPulso(Auth::id(), $request->input('sala'));

        return response()->json(['status' => 'ok']);
    }
}
