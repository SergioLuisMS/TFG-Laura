@extends('plantilla.app')

@section('content')
<div class="contenedor-perfil-layout" style="justify-content: center;">
    <div class="columna-perfil-der" style="flex: 0 1 800px;">
        <form action="{{ route('perfil.actualizar-avatar') }}" method="POST">
            @csrf
            @method('PUT')
            
            {{-- REUTILIZAMOS EL MISMO COMPONENTE AQUÍ TAMBIÉN --}}
            @include('componentes.form-avatar')

            <button type="submit" class="btn-primario" style="width: 100%; margin-top: 20px;">
                Guardar Nuevo Estilo 🎨
            </button>
        </form>
    </div>
</div>
{{-- El preview del avatar ya se carga desde app.js (importa avatar-preview.js globalmente) --}}
@endsection