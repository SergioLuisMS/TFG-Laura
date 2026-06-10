@extends('plantilla.app')

@section('content')

{{-- 1. Alertas más integradas --}}
@if(session('success'))
    <div class="alerta-patata-exitosa">
        {{ session('success') }}
    </div>
@endif

<div class="titulo-crema">
    <h1 class="titulo-principal">Más patatas en Patatas y Letras</h1>
    <p class="subtitulo-principal">Encuentra nuevos amigos para compartir lecturas</p>
</div>

<div class="contenedor-perfil-layout">

    {{-- COLUMNA IZQUIERDA: NAVEGACIÓN --}}
    <div class="columna-perfil-izq">
        <div class="tarjeta-navegacion-fija shadow-sm">
            <h3 class="titulo-menu">Amistades</h3>
            <div class="grupo-botones-vertical">
                
                <a href="{{ route('amigos.index', ['tab' => 'buscar']) }}"
                    class="btn-nav {{ request('tab', 'buscar') == 'buscar' ? 'active' : '' }}">
                    🔎 Más patatas
                </a>

                <a href="{{ route('amigos.index', ['tab' => 'mis-amigos']) }}"
                    class="btn-nav {{ request('tab') == 'mis-amigos' ? 'active' : '' }}">
                    👥 Mis amigos
                </a>

                <a href="{{ route('amigos.index', ['tab' => 'solicitudes']) }}"
                    class="btn-nav {{ request('tab') == 'solicitudes' ? 'active' : '' }} btn-notificacion">
                    <span>🔔 Solicitudes</span>
                    @if($solicitudesPendientes > 0)
                        <span class="badge-notificacion">{{ $solicitudesPendientes }}</span>
                    @endif
                </a>
            </div>
            <hr class="separador-menu">
            <a href="/" class="btn-nav">🏠 Volver al inicio</a>
        </div>
    </div>

    {{-- COLUMNA DERECHA: CONTENIDO DINÁMICO --}}
    <div class="columna-perfil-der">

        {{-- CASO 1: SOLICITUDES --}}
        @if(request('tab') == 'solicitudes')
            <h3 class="titulo-seccion">🔔 Solicitudes recibidas</h3>

            {{-- IMPORTANTE: La lógica de $solicitudesRecibidas ahora viene del Controlador --}}
            @if(isset($solicitudesRecibidas) && $solicitudesRecibidas->count() > 0)
                <div class="grid-usuarios-container">
                    @foreach($solicitudesRecibidas as $soli)
                        @if($soli->sender)
                            @include('amigos.tarjeta_usuario', ['user' => $soli->sender, 'tipo' => 'solicitud_recibida'])
                        @endif
                    @endforeach
                </div>
            @else
                <div class="estado-vacio">
                    <p>No tienes ninguna solicitud pendiente. 🥔</p>
                </div>
            @endif

        {{-- CASO 2: MIS AMIGOS --}}
        @elseif(request('tab') == 'mis-amigos')
            <h3 class="titulo-seccion">Mis amigos patatiles</h3>
            <div class="grid-usuarios-container">
                @forelse($misAmigos as $user)
                    @include('amigos.tarjeta_usuario', ['user' => $user, 'tipo' => 'gestion'])
                @empty
                    <div class="estado-vacio">
                        <p>Aún no tienes amigos confirmados.</p>
                    </div>
                @endforelse
            </div>

        {{-- CASO 3: DESCUBRIR (POR DEFECTO) --}}
        @else
            <h3 class="titulo-seccion">Descubrir nuevas patatas</h3>
            <div class="grid-usuarios-container">
                @forelse($usuarios as $user)
                    @include('amigos.tarjeta_usuario', ['user' => $user, 'tipo' => 'buscar'])
                @empty
                    <div class="estado-vacio">
                        <p>No hay mas patatas por descubrir.</p>
                    </div>
                @endforelse
            </div>

            {{-- Paginacion de la lista de usuarios por descubrir --}}
            @if($usuarios->hasPages())
                <div class="mt-6 flex justify-center">
                    {{ $usuarios->links() }}
                </div>
            @endif
        @endif

    </div>
</div>
@endsection