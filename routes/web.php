<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\AmigoController;

/*
|--------------------------------------------------------------------------
| 1. PÁGINA DE INICIO (Pública)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('menu');
})->name('home');

/*
|--------------------------------------------------------------------------
| 2. RUTAS DE ACCESO (Login y Registro)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');

// APLICAMOS EL RATE LIMITER AQUÍ:
// Permite máximo 5 intentos por minuto por cada dirección IP
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('registro');
Route::post('/registro', [AuthController::class, 'registrar']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
/*
|--------------------------------------------------------------------------
| 3. BÚSQUEDA DE LIBROS (Pública)
|--------------------------------------------------------------------------
*/
Route::get('/libros/buscar', [LibroController::class, 'buscar'])->name('libros.buscar');

/*
|--------------------------------------------------------------------------
| 4. RUTAS PROTEGIDAS (Solo para usuarios logueados)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // --- PERFIL Y AVATAR ---
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::get('/perfil/editar-avatar', [PerfilController::class, 'editarAvatar'])->name('perfil.editar-avatar');
    Route::put('/perfil/actualizar-avatar', [PerfilController::class, 'actualizarAvatar'])->name('perfil.actualizar-avatar');
    Route::post('/perfil/actualizar-nombre', [PerfilController::class, 'actualizarNombre'])->name('perfil.actualizarNombre');

    // --- ESTANTERÍA Y GESTIÓN DE LIBROS ---
    Route::get('/mi-estanteria', [LibroController::class, 'miEstanteria'])->name('libros.estanteria');
    Route::get('/estanteria/filtrar', [LibroController::class, 'filtrar'])->name('libros.filtrar');
    Route::get('/libros', [LibroController::class, 'inicio'])->name('libros.inicio');
    Route::post('/libros/guardar', [LibroController::class, 'guardar'])->name('libros.guardar');
    Route::delete('/libros/{libro}', [LibroController::class, 'eliminar'])->name('libros.eliminar');
    Route::put('/mi-estanteria/{libro}', [LibroController::class, 'actualizarEstanteria'])->name('libros.actualizar');

    // --- SALAS DE CONCENTRACIÓN ---
    Route::get('/salas', [SalaController::class, 'index'])->name('salas.index');
    Route::get('/salas/{tipo}', [SalaController::class, 'show'])->name('salas.show');
    Route::post('/salas/guardar', [SalaController::class, 'guardar'])->name('salas.guardar');
    Route::post('/salas/registrar-pulso', [SalaController::class, 'registrarPulso'])->name('salas.pulso');

    // --- SISTEMA DE AMIGOS ---
    Route::get('/buscar-amigos', [AmigoController::class, 'index'])->name('amigos.index');
    Route::post('/amigos/enviar/{id}', [AmigoController::class, 'enviarSolicitud'])->name('amigos.enviar');
    Route::post('/amigos/aceptar/{id}', [AmigoController::class, 'aceptarSolicitud'])->name('amigos.aceptar');
    Route::post('/amigos/rechazar/{id}', [AmigoController::class, 'rechazarSolicitud'])->name('amigos.rechazar');
    Route::delete('/amigos/eliminar/{id}', [AmigoController::class, 'eliminarAmigo'])->name('amigos.eliminar');

    // --- VISITAS A AMIGOS ---
    Route::get('/buscar-libros-amigo/{id}', [PerfilController::class, 'verEstanteriaAmigo'])->name('amigo.estanteria');

    // 🎯 CORRECCIÓN AQUÍ: Cambiamos AmigoController por PerfilController
    Route::get('/visitar-perfil/{id}', [PerfilController::class, 'visitarPerfil'])->name('amigos.visitar');
});
