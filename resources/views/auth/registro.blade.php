@extends('plantilla.app')

@section('content')

@if ($errors->any())
<div id="validation-alert" data-message="{{ $errors->first() }}" style="display:none;"></div>
@endif

<div class="seccion-auth">
    <div class="contenedor-auth-card">

        <h2 class="auth-titulo">Crear cuenta 🥔📚</h2>
        <p class="auth-subtitulo">Únete para empezar a crear tu avatar y guardar tus libros.</p>

        <form action="{{ route('registro') }}" method="POST">
            @csrf

            <div class="input-group">
                <label>Nombre</label>
                <input type="text" name="name" placeholder="Tu nombre completo" value="{{ old('name') }}" required>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Tu email" value="{{ old('email') }}" required>
            </div>

            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="Crea una contraseña" required>
            </div>

            <div class="input-group">
                <label>Repite la contraseña</label>
                <input type="password" name="password_confirmation" placeholder="Repite tu contraseña" required>
            </div>

            {{-- Selector de avatar --}}
            <div class="separador-avatar">
                @include('componentes.form-avatar')
            </div>

            <button type="submit" class="btn-primario">
                Registrarme ahora 🎨
            </button>
        </form>

        <div class="auth-footer">
            <p>
                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
            </p>
        </div>

    </div>
</div>

{{-- El preview del avatar ya se carga desde app.js (importa avatar-preview.js globalmente) --}}

@endsection