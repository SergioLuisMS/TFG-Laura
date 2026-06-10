@extends('plantilla.app')

@section('content')
<div class="seccion-auth">
    <div class="contenedor-menu-principal" style="padding-top: 20px;">

        {{-- 1. BUSCADOR HERO (Estilo buscador.css) --}}
        <div class="zona-busqueda-hero">
            <form action="{{ route('libros.buscar') }}" method="GET" id="form-busqueda" class="formulario-busqueda-gigante">
                <div class="input-wrapper-busqueda">
                    <input type="text" name="query" placeholder="Buscar libros..." value="{{ request('query') }}" required>
                    <button type="submit" id="btn-buscar" class="btn-buscar-estilo">🔍</button>
                </div>
            </form>
        </div>

        {{-- MENSAJE DE ÉXITO (Para sesiones tradicionales) --}}
        @if(session('success'))
        <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
            <div class="toast-exito">
                ✨ {{ session('success') }} 🥔
            </div>
        </div>
        @endif

        {{-- 2. LISTA DE RESULTADOS --}}
        @if(isset($libros) && count($libros) > 0)
        <div class="lista-resultados">
            @foreach($libros as $libro)
            <div class="tarjeta-libro-lista">
                {{-- Portada --}}
                <div style="flex-shrink: 0; margin-right: 25px;">
                    <img src="{{ $libro['portada'] }}" class="portada-libro-resultado" style="width: 100px; height: 140px;"
                        onerror="this.onerror=null;this.src='{{ asset('img/no-portada.svg') }}'">
                </div>

                {{-- Info --}}
                <div style="flex-grow: 1;">
                    <h3 class="auth-titulo" style="text-align: left; font-size: 1.3rem;">{{ Str::limit($libro['titulo'], 70) }}</h3>
                    <p class="auth-subtitulo" style="text-align: left;">{{ $libro['autor'] }}</p>
                    <span class="etiqueta-genero">{{ $libro['genero'] }}</span>
                </div>

                {{-- Acción (AJAX) --}}
                <div class="acciones-libro">
                    @auth
                    <button type="button"
                        class="btn-compacto-add"
                        onclick="añadirLibroSinRecargar(this)"
                        data-title="{{ $libro['titulo'] }}"
                        data-author="{{ $libro['autor'] }}"
                        data-genre="{{ $libro['genero'] }}"
                        data-cover="{{ $libro['portada'] }}">
                        + Añadir
                    </button>
                    @else
                    <button type="button" class="js-invitado btn-compacto-add" style="background: #9ca3af;">
                        + Añadir
                    </button>
                    @endauth
                </div>
            </div>
            @endforeach
        </div>

        @elseif(request('query'))
        {{-- Estado vacío --}}
        <div class="busqueda-vacia" style="text-align: center; padding: 40px;">
            <p style="color: #8b5e3c; font-size: 1.2rem;">No hay patatas... digo, libros. 🥔</p>
            <a href="{{ route('libros.buscar') }}" class="btn-buscar-estilo" style="text-decoration: none; display: inline-block; margin-top: 15px;">
                ← Volver a intentar
            </a>
        </div>
        @endif

    </div>
</div>
@endsection