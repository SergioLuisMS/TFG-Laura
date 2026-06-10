@extends('plantilla.app')

@section('content')
<div class="contenedor-estanteria-manual">
    <h2 class="titulo-biblioteca">Mi Estantería de Patatas 🥔</h2>

    {{-- Barra de filtros por género --}}
    <div class="barra-filtros">
        <button class="btn-filtro active" data-genero="todos">📚 Todos</button>
        <button class="btn-filtro" data-genero="Romántica">💖 Romántica</button>
        <button class="btn-filtro" data-genero="Fantasía">🧚‍♀️ Fantasía</button>
        <button class="btn-filtro" data-genero="Policiaca">🔍 Policiaca</button>
        <button class="btn-filtro" data-genero="Terror">👻 Terror</button>
        <button class="btn-filtro" data-genero="Ciencia Ficción">🚀 Ciencia Ficción</button>
        <button class="btn-filtro" data-genero="Aventura">🗺️ Aventura</button>
        <button class="btn-filtro" data-genero="Historia">📜 Historia</button>
        <button class="btn-filtro" data-genero="Clásicos">🏛️ Clásicos</button>
        <button class="btn-filtro" data-genero="Narrativa">📖 Narrativa</button>
    </div>

    {{-- 1. Quitamos el ">" extra que rompía el Grid --}}
    <div class="rejilla-libros" id="contenedor-libros-ajax">
        @forelse($books as $book)
        <div class="tarjeta-libro-estanteria">

            <div class="contenedor-portada-estanteria">
                <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="img-portada-estanteria">
            </div>

            <div class="cuerpo-tarjeta-estanteria">
                <h3 class="titulo-estanteria">{{ $book->title }}</h3>
                <p class="autor-estanteria">{{ $book->author }}</p>

                <div class="separador-tarjeta"></div>

                <form action="{{ route('libros.actualizar', $book->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="fila-control">
                        <span style="font-weight: 700; color: #8b5e3c;">Género</span>
                        <strong class="etiqueta-lectura">
                            @switch($book->genre)
                            @case('Romántica') 💖 Romántica @break
                            @case('Romance') 💖 Romántica @break
                            @case('Fantasía') 🧚‍♀️ Fantasía @break
                            @case('Policiaca') 🔍 Policiaca @break
                            @case('Terror') 👻 Terror @break
                            @case('Ciencia Ficción') 🚀 Ciencia Ficción @break
                            @case('Aventura') 🗺️ Aventura @break
                            @case('Historia') 📜 Historia @break
                            @case('Clásicos') 🏛️ Clásicos @break
                            @case('Narrativa') 📖 Narrativa @break
                            @default 📚 {{ $book->genre ?? 'Narrativa' }}
                            @endswitch
                        </strong>
                    </div>

                    <div class="fila-control">
                        <span style="font-weight: 700; color: #8b5e3c;">Estado</span>
                            <select name="estado" onchange="this.form.submit()" class="select-estanteria">
                                <option value="por_leer" {{ $book->pivot->estado == 'por_leer' ? 'selected' : '' }}>📖 Por leer</option>
                                <option value="leyendo" {{ $book->pivot->estado == 'leyendo' ? 'selected' : '' }}>👓 Leyendo</option>
                                <option value="leido" {{ $book->pivot->estado == 'leido' ? 'selected' : '' }}>✅ Leído</option>
                            </select>
                    </div>

                    <div class="fila-control">
                        <span>Nota</span>
                        <select name="puntuacion" onchange="this.form.submit()" class="select-estanteria nota-patata">
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ $book->pivot->puntuacion == $i ? 'selected' : '' }}>
                                {{ str_repeat('🥔', $i) }}
                                </option>
                                @endfor
                        </select>
                    </div>
                </form>

                <form action="{{ route('libros.eliminar', $book->id) }}" method="POST" onsubmit="return confirm('¿Borrar esta patata?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-eliminar-manual">
                        Eliminar de la estanteria
                    </button>
                </form>
            </div>
        </div>
        @empty
        <p style="color: white; text-align: center; width: 100%;">Aún no tienes libros. 🥔</p>
        @endforelse
    </div> {{-- Cierre de rejilla-libros --}}

    {{-- Paginacion: solo se muestra cuando hay mas de una pagina de libros --}}
    @if($books->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $books->links() }}
        </div>
    @endif

</div> {{-- Cierre de contenedor-estanteria-manual --}}
@endsection