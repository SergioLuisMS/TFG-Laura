<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\AmigoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. PAGINA DE INICIO (Publica)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    // Calculo las solicitudes pendientes solo si hay sesion activa
    $solicitudesPendientes = 0;
    if (auth()->check()) {
        $solicitudesPendientes = \App\Models\Amigo::where('amigo_id', auth()->id())
            ->where('estado', 'pendiente')
            ->count();
    }
    return view('menu', compact('solicitudesPendientes'));
})->name('home');

/*
|--------------------------------------------------------------------------
| 2. RUTAS DE ACCESO (Login y Registro)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('registro');
Route::post('/registro', [AuthController::class, 'registrar'])->middleware('throttle:3,1');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| 3. VERIFICACION DE EMAIL (Seguridad #27)
| Genera las rutas /email/verify y /email/verification-notification.
| Para activar la barrera de acceso, anade ->middleware('verified') al grupo
| de rutas protegidas de abajo. Requiere configurar MAIL_MAILER en .env.
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/')->with('success', 'Email verificado correctamente.');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Enlace de verificacion reenviado.');
    })->middleware('throttle:6,1')->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| 4. BUSQUEDA DE LIBROS (Publica)
|--------------------------------------------------------------------------
*/
Route::get('/libros/buscar', [LibroController::class, 'buscar'])->name('libros.buscar');

/*
|--------------------------------------------------------------------------
| 5. RUTAS PROTEGIDAS (Solo usuarios logueados)
| Para activar la verificacion de email, anade 'verified' al array:
| Route::middleware(['auth', 'verified'])->group(...)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // --- PERFIL Y AVATAR ---
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::get('/perfil/editar-avatar', [PerfilController::class, 'editarAvatar'])->name('perfil.editar-avatar');
    Route::put('/perfil/actualizar-avatar', [PerfilController::class, 'actualizarAvatar'])->name('perfil.actualizar-avatar');
    Route::post('/perfil/actualizar-nombre', [PerfilController::class, 'actualizarNombre'])->name('perfil.actualizarNombre');

    // --- ESTANTERIA Y GESTION DE LIBROS ---
    Route::get('/mi-estanteria', [LibroController::class, 'miEstanteria'])->name('libros.estanteria');
    Route::get('/estanteria/filtrar', [LibroController::class, 'filtrar'])->name('libros.filtrar');
    Route::get('/libros', [LibroController::class, 'inicio'])->name('libros.inicio');
    Route::post('/libros/guardar', [LibroController::class, 'guardar'])->name('libros.guardar');
    Route::delete('/libros/{libro}', [LibroController::class, 'eliminar'])->name('libros.eliminar');
    Route::put('/mi-estanteria/{libro}', [LibroController::class, 'actualizarEstanteria'])->name('libros.actualizar');

    // --- SALAS DE CONCENTRACION ---
    Route::get('/salas', [SalaController::class, 'index'])->name('salas.index');
    Route::get('/salas/{tipo}', [SalaController::class, 'show'])->name('salas.show');
    Route::post('/salas/guardar', [SalaController::class, 'guardar'])->name('salas.guardar');

    // Pulso automatico: limito a 2 llamadas por minuto para evitar flood (Seguridad #28)
    Route::post('/salas/registrar-pulso', [SalaController::class, 'registrarPulso'])
        ->middleware('throttle:2,1')
        ->name('salas.pulso');

    // --- SISTEMA DE AMIGOS ---
    Route::get('/buscar-amigos', [AmigoController::class, 'index'])->name('amigos.index');
    Route::post('/amigos/enviar/{id}', [AmigoController::class, 'enviarSolicitud'])->name('amigos.enviar');
    Route::post('/amigos/aceptar/{id}', [AmigoController::class, 'aceptarSolicitud'])->name('amigos.aceptar');
    Route::post('/amigos/rechazar/{id}', [AmigoController::class, 'rechazarSolicitud'])->name('amigos.rechazar');
    Route::delete('/amigos/eliminar/{id}', [AmigoController::class, 'eliminarAmigo'])->name('amigos.eliminar');

    // --- VISITAS A AMIGOS ---
    Route::get('/visitar-perfil/{id}', [PerfilController::class, 'visitarPerfil'])->name('amigos.visitar');

    // --- CHAT DE SALAS ---
    // Limito el envio de mensajes a 30 por minuto para evitar spam (Seguridad #28)
    Route::post('/chat/enviar', [ChatController::class, 'enviar'])
        ->middleware('throttle:30,1')
        ->name('chat.enviar');

    Route::get('/chat/obtener', [ChatController::class, 'obtener'])->name('chat.obtener');
});
