@extends('plantilla.app')

@section('content')
<div class="contenedor-menu-principal">

    {{-- Título principal --}}
    <div class="caja-bienvenida">
        <h1 class="titulo-patata">¡Bienvenida a la Red de Patatas! 🥔</h1>

        @auth
        <p class="subtitulo-menu">Hola de nuevo, <strong>{{ Auth::user()->name }}</strong>. ¿Qué vamos a leer hoy?</p>
        @endauth

        @guest
        <div class="info-invitado">
            <p>¡Hola! <strong>Únete a la red</strong> para personalizar tu patata y guardar tus lecturas favoritas.</p>
        </div>
        @endguest
    </div>

    {{-- Grid de Tarjetas --}}
    <div class="grid-menu">

        @auth
        {{-- BLOQUE: Mi Biblioteca --}}
        <div class="tarjeta-menu">
            <div class="icono-tarjeta">📖</div>
            <h3>Mi Biblioteca</h3>
            <p>Gestiona tus lecturas guardadas.</p>
            <a href="{{ route('libros.estanteria') }}" class="btn-menu">Ver mis libros</a>
        </div>

        {{-- BLOQUE: Concentración --}}
        <div class="tarjeta-menu">
            <div class="icono-tarjeta">🏠</div>
            <h3>Concentración</h3>
            <p>Registra tu tiempo de lectura.</p>
            <a href="{{ route('salas.index') }}" class="btn-menu">Ir a una sala</a>
        </div>

        {{-- BLOQUE: Amigos --}}
        <div class="tarjeta-menu">
            <div class="icono-tarjeta">
                🥔
                @if($solicitudesPendientes > 0)
                <span class="burbuja-notificacion">{{ $solicitudesPendientes }}</span>
                @endif
            </div>
            <h3>Más patatas</h3>
            <p>Conoce y conecta con otros lectores.</p>
            <a href="{{ route('amigos.index') }}" class="btn-menu">Ver comunidad</a>
        </div>
        @endauth

        {{-- BLOQUE: Explorar (Visible para todos) --}}
        <div class="tarjeta-menu">
            <div class="icono-tarjeta">🔍</div>
            <h3>Explorar</h3>
            <p>Busca nuevos libros en Google Books.</p>
            <a href="{{ route('libros.buscar') }}" class="btn-menu btn-oscuro">Buscar libros</a>
        </div>

        @guest
        {{-- BLOQUE: Registro --}}
        <div class="tarjeta-menu tarjeta-resaltada">
            <div class="icono-tarjeta">✨</div>
            <h3>¡Únete!</h3>
            <p>Crea tu cuenta y guarda tus lecturas. ¡Tambien puedes registrar tu tiempo de lectura!</p>
            <a href="{{ route('registro') }}" class="btn-menu">Registrarme ahora</a>
        </div>
        @endguest

    </div>

</div>
@endsection