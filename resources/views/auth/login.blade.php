@extends('plantilla.app')

@section('content')

@if ($errors->any())
<div id="validation-alert" data-message="{{ $errors->first() }}" style="display:none;"></div>
@endif

<div class="seccion-auth">
    <div class="contenedor-auth-card">
        <h2 class="auth-titulo">¡Hola de nuevo! 🥔📚</h2>
        <p class="auth-subtitulo">Entra para ver cómo va tu estantería de libros.</p>

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="tu@email.com" value="{{ old('email') }}" required>
            </div>

            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="Tu contraseña secreta" required>
            </div>

            <button type="submit" class="btn-primario">
                Entrar a mi cuenta 🚀
            </button>
        </form>

        <div class="auth-footer">
            <p>
                ¿No tienes cuenta?
                <a href="{{ route('registro') }}">Regístrate aquí</a>
            </p>
        </div>
    </div>
</div>

@endsection