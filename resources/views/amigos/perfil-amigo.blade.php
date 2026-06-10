@extends('plantilla.app')

@section('content')

<div class="contenedor-perfil-layout">

    {{-- COLUMNA IZQUIERDA (1/3): IDENTIDAD --}}
    <div class="columna-perfil-izq">
        <div class="tarjeta-decorativa shadow-sm">

            {{-- Avatar --}}
            <div class="circulo-avatar-grande">
                <img src="{{ asset('img/avatar/base/' . basename($amigo->avatar_base ?? 'azulRelleno.png')) }}"
                    class="capa-v-avatar" style="z-index: 1;">

                <img src="{{ asset('img/avatar/ojos/' . basename($amigo->avatar_ojos ?? 'ojos1.png')) }}"
                    class="capa-v-avatar" style="z-index: 2;">

                <img src="{{ asset('img/avatar/boca/' . basename($amigo->avatar_boca ?? 'boca1.png')) }}"
                    class="capa-v-avatar mix-blend" style="z-index: 3;">

                @if($amigo->avatar_complemento)
                <img src="{{ asset('img/avatar/complemento/' . basename($amigo->avatar_complemento)) }}"
                    class="capa-v-avatar" style="z-index: 4;">
                @endif
            </div>

            <h3 class="nombre-usuario">
                {{ $amigo->name }}
            </h3>

            <p class="txt-decorativo">Nivel de amistad: Máximo 🥔✨</p>

            <hr class="separador-perfil">

            <div class="grupo-botones-vertical">
                <button class="btn-perfil-navegacion btn-tab-amigo active" data-target="tab-estanteria">
                    📚 Estantería
                </button>

                <button class="btn-perfil-navegacion btn-tab-amigo" data-target="tab-info">
                    ℹ️ Información
                </button>

                <a href="{{ route('amigos.index', ['tab' => 'mis-amigos']) }}" class="btn-perfil-navegacion">
                    ⬅ Volver
                </a>
            </div>
        </div>
    </div>

    {{-- COLUMNA DERECHA (2/3): CONTENIDO --}}
    <div class="columna-perfil-der">
        {{-- El marco transparente que ahora controla el CSS --}}
        <div class="tarjeta-decorativa">
            <div class="panel-contenido-principal">

                {{-- PESTAÑA 1: ESTANTERÍA --}}
                <div id="tab-estanteria" class="contenido-tab">
                    {{-- Nuevo Bloque: Cabecera Estructurada --}}
                    <div class="header-biblioteca">
                        <h2 class="titulo-biblioteca">📚 Biblioteca de {{ $amigo->name }}</h2>
                        <span class="stats-biblioteca">{{ $books->count() }} Libros</span>
                    </div>

                    <div class="seccion-filtros">
                        <p class="texto-informativo">Echa un vistazo a lo que está leyendo tu amigo...</p>
                    </div>

                    <div class="rejilla-libros-amigo">
                        @forelse($books as $book)
                        <div class="tarjeta-libro-estanteria">
                            <div class="contenedor-portada-estanteria">
                                <img src="{{ $book->cover_url }}" alt="{{ $book->title }}">
                            </div>

                            <div class="info-libro-mini">
                                <h4 class="titulo-libro-amigo">{{ $book->title }}</h4>

                                <div class="badge-estado">
                                    @if($book->pivot->estado == 'leyendo') 👓 Leyendo
                                    @elseif($book->pivot->estado == 'leido') ✅ Leído
                                    @else 📖 Por leer @endif
                                </div>

                                <div class="puntuacion-patatas">
                                    {{ str_repeat('🥔', $book->pivot->puntuacion ?? 0) }}
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="zona-vacia-estanteria">
                            <span>📖</span>
                            <p>{{ $amigo->name }} aún no tiene libros en su estantería.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- PESTAÑA 2: INFORMACIÓN --}}
                <div id="tab-info" class="contenido-tab" style="display: none;">
                    <div class="header-biblioteca">
                        <h2 class="titulo-biblioteca">ℹ️ Sobre {{ $amigo->name }}</h2>
                    </div>
                    <div class="seccion-filtros">
                        <p class="texto-informativo">Detalles de la cuenta y actividad.</p>
                    </div>

                    <div class="info-amigo-container">
                        <div class="info-item">
                            <p><strong>📅 Miembro desde:</strong> {{ $amigo->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-tab-amigo').forEach(boton => {
        boton.addEventListener('click', () => {
            document.querySelectorAll('.contenido-tab').forEach(cont => cont.style.display = 'none');
            const target = document.getElementById(boton.getAttribute('data-target'));
            if (target) target.style.display = 'block';
            document.querySelectorAll('.btn-tab-amigo').forEach(b => b.classList.remove('active'));
            boton.classList.add('active');
        });
    });
</script>
@endsection