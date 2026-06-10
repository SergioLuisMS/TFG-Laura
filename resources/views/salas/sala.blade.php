@extends('plantilla.app')

@section('clase-body', 'esta-logueado sala-' . $tipo)

@section('content')

    @php
        $fondos = [
            'botica' => 'fondo-botica2.png',
            'despacho-rosa' => 'fondo-despacho-rosa.png',
            'jardin' => 'fondo-jardin.png',
            'dormitorio' => 'fondo-dormitorio.png',
            'biblioteca' => 'fondo-biblioteca.png',
            'despacho-neutro' => 'fondo-despacho-neutro.png',
        ];
        $fondoActual = $fondos[$tipo] ?? 'fondo-botica.png';
    @endphp

    <div class="pantalla-estudio" id="sala-interactiva-root" data-tipo="{{ $tipo }}" data-user="{{ Auth::user()->name }}">

        <div class="capa-mapa" style="position: relative; display: inline-block;">
            <img src="{{ asset('img/fondo/' . $fondoActual) }}" usemap="#image-map" id="fondo-img">

            @if($tipo === 'botica')
                {{-- Elementos específicos de Botica --}}
                <img src="{{ asset('img/items/botica/cajon-vacio.png') }}" id="cajon-overlay" class="overlay-item"
                    style="z-index:2; display:none;">
                <div id="reacciones-contenedor">
                    <img src="{{ asset('img/items/botica/caldero/caldero1.png') }}" id="reaccion-bote1" class="reaccion-caldero"
                        style="display:none;">
                    <img src="{{ asset('img/items/botica/caldero/caldero2.png') }}" id="reaccion-bote2" class="reaccion-caldero"
                        style="display:none;">
                    <img src="{{ asset('img/items/botica/caldero/caldero3.png') }}" id="reaccion-bote3" class="reaccion-caldero"
                        style="display:none;">
                    <img src="{{ asset('img/items/botica/caldero/caldero4.png') }}" id="reaccion-bote4" class="reaccion-caldero"
                        style="display:none;">
                </div>

                <img src="{{ asset('img/items/botica/bote/bote1.png') }}" class="bote-interactivo" id="bote1"
                    style="top: 44%; left: 74%;">
                <img src="{{ asset('img/items/botica/bote/bote2.png') }}" class="bote-interactivo" id="bote2"
                    style="top: 40%; left: 70%;">
                <img src="{{ asset('img/items/botica/bote/bote8.png') }}" class="bote-interactivo" id="bote4"
                    style="top: 55%; left: 69%;">
                <img src="{{ asset('img/items/botica/bote/bote3.png') }}" class="bote-interactivo" id="bote3"
                    style="top: 61%; left: 71%;">

                <map name="image-map">
                    <area alt="cajon" coords="943,513,942,608,1019,629,1029,533" shape="poly" href="javascript:void(0);"
                        id="area-cajon">
                    <area id="area-caldero" coords="518,477,657,474,630,574,510,540" shape="poly" href="javascript:void(0);">
                </map>
            @endif

            {{-- 🥔 CHAT SIEMPRE DISPONIBLE --}}
            <div class="ventana-comentarios">
                <div class="chat-header"><span>PATATA-LOG ({{ strtoupper($tipo) }})</span></div>
                <div class="chat-messages" id="chat-box"></div>
                <div class="chat-input-area">
                    <input type="text" id="chat-input" placeholder="Escribe algo...">
                    <button id="btn-enviar-chat">➤</button>
                </div>
            </div>
        </div>

        <div class="contenido-sala-minimal">
            <div class="cabecera-discreta">
                <h1>{{ $sala['titulo'] }}</h1>
            </div>

            {{-- CRONÓMETRO GLOBAL (FUERA DE CONDICIONALES) --}}
            <div class="widget-concentracion">

                <div class="cronometro-circular" style="border-color: {{ $sala['color_borde'] ?? '#ccc' }}">

                    <div class="reloj-brillo"></div>

                    <span class="tiempo-display" id="timer">
                        00:00:00
                    </span>
                </div>

                <div class="focus-container">
                    <div id="focus-bar"></div>
                </div>

                <div class="focus-texto">
                    Concentración
                </div>

            </div>

            <div class="botones-inferiores">
                <a href="{{ route('perfil') }}" id="btn-terminar-sesion" class="btn-estudio btn-finalizar"
                    onclick="finalizarSesion(event)">TERMINAR</a>
                <a href="{{ route('salas.index') }}" class="btn-estudio btn-cambiar-sala">CAMBIAR SALA</a>
            </div>
        </div>
    </div>
@endsection