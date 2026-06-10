<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @yield('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="route-guardar-libro" content="{{ route('libros.guardar') }}">

    <title>Libros y patatas</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo/logo_patata.png') }}">

    {{-- Llamamos solo a app.css y app.js porque app.css ya importa todos los componentes --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(file_exists(public_path('css/fix-list.css')))
        <link rel="stylesheet" href="{{ asset('css/fix-list.css') }}">
    @endif
</head>

<body class="antialiased {{ Auth::check() ? 'esta-logueado' : 'es-invitado' }}"
      data-sala="@yield('clase-body', 'otra')">

    {{-- Nav principal --}}
    @include('componentes.nav')

    {{-- 🥔 Sistema de Alertas unificado (Éxito, Error y AJAX) --}}
    @if(View::exists('componentes.alertas'))
        @include('componentes.alertas')
    @else
        <div id="alerta-ajax">
            <div id="alerta-mensaje"></div>
        </div>
    @endif

    <main class="diseño-contenido-principal">
        @yield('content')
    </main>

   
</body>
</html>