@extends('plantilla.app')

@section('content')
<section class="seccion-auth">
    <div class="contenedor-auth-card" style="max-width: 850px; margin: 0 auto;">

        {{-- 1. BUSCADOR INTEGRADO (Nuevo) --}}
        <div class="zona-busqueda-resultados" style="margin-bottom: 30px;">
            <form action="{{ route('libros.buscar') }}" method="GET" style="display: flex; gap: 10px;">
                <div style="flex: 1; position: relative;">
                    <input type="text" name="query"
                        value="{{ request('query') }}"
                        placeholder="Buscar otro libro..."
                        style="width: 100%; padding: 12px 20px; border-radius: 20px; border: 2px solid #fed7aa; background: #fffaf5; outline: none; font-size: 1rem; color: #5d4037;">
                </div>
                <button type="submit" style="background: #fb923c; color: white; border: none; padding: 10px 25px; border-radius: 20px; font-weight: bold; cursor: pointer; transition: 0.3s;">
                    🔍
                </button>
            </form>
        </div>

        <h1 class="titulo-invitado">🥔 Resultados para: <span style="font-weight: 400;">"{{ request('query') }}"</span></h1>

        <div class="enlace-volver" style="margin-bottom: 20px;">
            <a href="{{ route('libros.estanteria') }}">← Ir a mi estantería</a>
        </div>

        <div class="lista-resultados">
            @forelse($libros as $libro)
            <div class="fila-libro">
                <img src="{{ $libro['portada'] }}" class="portada-libro-resultado"
                    onerror="this.onerror=null;this.src='{{ asset('img/no-portada.svg') }}'">

                <div class="info-libro">
                    <h3>{{ $libro['titulo'] }}</h3>
                    <p>{{ $libro['autor'] }}</p>
                    <span class="etiqueta-genero">
                        {{ $libro['genero'] }}
                    </span>
                </div>

                <div class="acciones-libro">
                    @auth
                    <button class="btn-compacto-add btn-añadir-libro"
                        onclick="añadirLibroSinRecargar(this)"
                        data-title="{{ $libro['titulo'] }}"
                        data-author="{{ $libro['autor'] }}"
                        data-genre="{{ $libro['genero'] }}"
                        data-cover="{{ $libro['portada'] }}">
                        + Añadir
                    </button>
                    @endauth
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 40px; background: white; border-radius: 20px;">
                <p style="color: #8b5e3c; font-size: 1.1rem;">No hay patatas... digo, libros para "{{ request('query') }}". 🥔</p>
            </div>
            @endforelse
        </div>

        <div class="enlace-volver" style="margin-top: 30px; text-align: center;">
            <a href="/">← Volver al inicio</a>
        </div>
    </div>
</section>

@endsection