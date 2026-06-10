@extends('plantilla.app')

@section('content')
{{-- Ya no hace falta el @vite aquí dentro --}}

<div class="contenedor-perfil-layout">
    
    {{-- COLUMNA IZQUIERDA (1/3): NAVEGACIÓN --}}
    <div class="columna-perfil-izq">
        <div class="tarjeta-decorativa shadow-sm">
            <div class="cuerpo-tarjeta-navegacion">
                <h3 class="libro-titulo-compacto">Navegación</h3>

                <div class="grupo-botones-vertical">
                    <a href="{{ route('libros.estanteria') }}" class="btn-perfil-navegacion">
                        📚 Mi Estantería
                    </a>

                    <a href="{{ url('/buscar-amigos?tab=mis-amigos') }}" class="btn-perfil-navegacion">
                        👥 Mis Amigos
                    </a>

                    <a href="{{ url('/salas') }}" class="btn-perfil-navegacion">
                        🏠 Salas
                    </a>
                </div>

                <hr class="separador-perfil">

                <p class="txt-decorativo">
                    "Un libro es un jardín que se lleva en el bolsillo."
                </p>
            </div>
        </div>
    </div>

    {{-- COLUMNA DERECHA (2/3): GESTIÓN DE PERFIL --}}
    <div class="columna-perfil-der">

        <div class="caja-avatar-perfil">
            {{-- 🥔 AQUÍ EL NOMBRE DEL USUARIO --}}
            <h2 class="nombre-usuario-perfil" style="margin: 15px 0 5px 0; color: #7c2d12; font-size: 1.8rem; font-weight: 800; text-align: center;">
                {{ Auth::user()->name }}
            </h2>

            <div class="circulo-avatar-grande">
                <img src="{{ asset('img/avatar/' . Auth::user()->avatar_base) }}" class="capa-v-avatar">
                <img src="{{ asset('img/avatar/' . Auth::user()->avatar_boca) }}" class="capa-v-avatar mix-blend">
                <img src="{{ asset('img/avatar/' . Auth::user()->avatar_ojos) }}" class="capa-v-avatar">
                <img src="{{ asset('img/avatar/' . Auth::user()->avatar_complemento) }}" class="capa-v-avatar">


            </div>



            <div class="acciones-perfil">
                <a href="{{ route('perfil.editar-avatar') }}" class="btn-perfil-accion">🎨 Cambiar Avatar</a>
                {{-- ✏️ Este botón usa la función global definida en app.js --}}
                <button onclick="toggleFormNombre()" class="btn-perfil-accion">✏️ Cambiar Nombre</button>
            </div>

            {{-- FORMULARIO DE CAMBIO DE NOMBRE --}}
            <div id="form-nombre-container" style="display: none; margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.5); border-radius: 15px; border: 1px solid #fed7aa; width: 100%; max-width: 400px;">
                <form action="{{ route('perfil.actualizarNombre') }}" method="POST" style="display: flex; flex-direction: column; gap: 10px;">
                    @csrf
                    <input type="text" name="name" value="{{ Auth::user()->name }}"
                        class="input-buscador-ancho" style="padding: 8px; font-size: 0.9rem; width: 100%; box-sizing: border-box;" required>

                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" onclick="toggleFormNombre()" style="background: none; border: none; cursor: pointer; font-size: 0.7rem; color: #666; font-weight: bold; text-transform: uppercase;">Cancelar</button>
                        <button type="submit" class="btn-compacto-add">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <details class="datos-usuario-desplegable">
            <summary>📊 Mis Estadísticas Patatiles</summary>
            <div class="contenido-datos">
                <p><strong>Nombre:</strong> {{ Auth::user()->name }}</p>
                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>

                <hr class="separador-perfil" style="margin: 10px 0;">

                <p>
                    <strong>⭐ Género más valorado:</strong>
                    @if(isset($estadisticasGeneros) && $estadisticasGeneros->count() > 0)
                    <span style="color: #fb923c; font-weight: 800;">
                        {{ $estadisticasGeneros->first()->genre ?? 'Narrativa' }}
                    </span>
                    <small>({{ number_format($estadisticasGeneros->first()->media_puntuacion, 1) }} ★)</small>
                    @else
                    <span style="color: #94a3b8; font-style: italic;">¡Aún no has puntuado nada!</span>
                    @endif
                </p>

                <div class="seccion-tiempo">
                    <strong>⏱️ Tiempo de enfoque:</strong>
                    @if(isset($tiemposPorSala) && $tiemposPorSala->count() > 0)
                    <ul style="list-style: none; padding-left: 10px; margin-top: 5px;">
                        @foreach($tiemposPorSala as $sesion)
                        @php
                        // 1. Limpiamos cualquier texto sobrante (clases del body o prefijos)
                        $salaLimpia = str_replace(['esta-logueado', 'sala-', 'sala', '-'], [' ', '', '', ' '], $sesion->sala);
                        // 2. Lo ponemos bonito (Primera letra mayúscula)
                        $nombreFinal = ucwords(trim($salaLimpia));
                        @endphp
                        <li style="font-size: 0.9rem;">
                            📍 {{ $nombreFinal }}:
                            <strong>{{ floor($sesion->total_segundos / 60) }}m {{ $sesion->total_segundos % 60 }}s</strong>
                        </li>
                        @endforeach
                    </ul>

                    {{-- 🥔 Cálculo del TOTAL real --}}
                    <div style="margin-top: 8px; border-top: 1px dashed #fdba74; padding-top: 5px; font-size: 1rem;">
                        @php
                        $segundosTotalesGlobal = $tiemposPorSala->sum('total_segundos');
                        $horas = floor($segundosTotalesGlobal / 3600);
                        $minutos = floor(($segundosTotalesGlobal % 3600) / 60);
                        @endphp
                        <strong>Total acumulado:</strong>
                        @if($horas > 0) {{ $horas }}h @endif {{ $minutos }} minutos
                    </div>
                    @else
                    <p style="color: #94a3b8; font-style: italic; margin-top: 5px;">¡Todavía no has entrado en ninguna sala!</p>
                    @endif
                </div>

                <p style="margin-top: 15px;"><strong>ADN Patata:</strong> {{ Auth::user()->avatar_base }}, {{ Auth::user()->avatar_ojos }}</p>
            </div>
        </details>

        <div style="margin-top: 30px;">
            <a href="/" class="btn-primario" style="font-size: 0.8rem; padding: 10px 20px; text-decoration: none;">⬅ Volver al menú</a>
        </div>
    </div>

</div>
{{-- 🏁 FIN DEL CONTENIDO: El JS ya no vive aquí, vive en app.js --}}
@endsection