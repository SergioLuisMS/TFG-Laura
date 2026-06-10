{{-- resources/views/libros/lista-libros-estanteria.blade.php --}}
@forelse($books as $book)
<div class="tarjeta-libro-estanteria">
    <div class="contenedor-portada-estanteria">
        <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="img-portada-estanteria">
    </div>

    <div class="cuerpo-tarjeta-estanteria">
        <h3 class="titulo-estanteria">{{ $book->title }}</h3>
        <p class="autor-estanteria">{{ $book->author }}</p>

        <div class="separador-tarjeta"></div>

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

        <form action="{{ route('libros.actualizar', $book->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="fila-control">
                <span>Estado</span>
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

        <form action="{{ route('libros.eliminar', $book) }}" method="POST" onsubmit="return confirm('¿Borrar esta patata?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-eliminar-manual">Eliminar de la red</button>
        </form>
    </div>
</div>
@empty
<p style="color: white; text-align: center; width: 100%;">No hay libros en esta categoría. 🥔</p>
@endforelse