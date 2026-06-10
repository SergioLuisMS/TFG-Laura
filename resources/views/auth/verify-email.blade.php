@extends('plantilla.app')

@section('content')
<div class="seccion-auth">
    <div class="contenedor-auth-card">
        <h2>Verifica tu correo</h2>

        <p style="margin-bottom: 1rem;">
            Antes de continuar, revisa tu bandeja de entrada. Te hemos enviado un enlace de verificacion.
        </p>

        @if(session('success'))
            <div class="alerta-patata-exitosa" style="margin-bottom: 1rem;">
                {{ session('success') }}
            </div>
        @endif

        <p style="color: #666; font-size: 0.9rem; margin-bottom: 1.5rem;">
            Si no recibes el correo, puedes solicitar uno nuevo.
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-primary">Reenviar enlace de verificacion</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="margin-top: 1rem;">
            @csrf
            <button type="submit" class="btn-secundario">Cerrar sesion</button>
        </form>
    </div>
</div>
@endsection
