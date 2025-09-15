<?php

use Illuminate\Http\Request;

use App\Http\Controllers\AccionController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\RedSocialController;
use App\Http\Controllers\EventoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\NotificacionController;


use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|*/

Route::post('/registro', [AuthController::class, 'registro']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('mongo.auth')->group(function () {
    Route::post('/cerrarsesion', [AuthController::class, 'cerrarsesion']);
    Route::get('/usuario', function (Request $request) {
        return $request->user();
    });
});

// Roles
Route::controller(RolController::class)->group(function () {
    Route::get('/roles', 'index'); // List all roles
    Route::post('/roles', 'store'); // Create a new role
    Route::get('/roles/{id}', 'show'); // Get a specific role
    Route::put('/roles/{id}', 'update'); // Update a specific role
    Route::delete('/roles/{id}', 'destroy'); // Delete a specific role
});

// Usuarios
Route::controller(UsuarioController::class)->group(function () {
    Route::get('/usuarios', 'index'); // List all users
    Route::post('/usuarios', 'store'); // Create a new user
    Route::get('/usuarios/{id}', 'show'); // Get a specific user
    Route::put('/usuarios/{id}', 'update'); // Update a specific user
    Route::delete('/usuarios/{id}', 'destroy'); // Delete a specific user
});

// Auditoria
Route::controller(AuditoriaController::class)->group(function () {
    Route::get('/auditoria', 'index'); // List all users
    Route::post('/auditoria', 'store'); // Create a new user
    Route::get('/auditoria/{id}', 'show'); // Get a specific user
    Route::put('/auditoria/{id}', 'update'); // Update a specific user
    Route::delete('/auditoria/{id}', 'destroy'); // Delete a specific user
});

/// Tablas Principales

// Album
Route::controller(AlbumController::class)->group(function () {
    Route::get('/album', 'index'); // List all social networks
    Route::post('/album', 'store'); // Create a new social network
    Route::get('/album/{id}', 'show'); // Get a specific social network
    Route::put('/album/{id}', 'update'); // Update a specific social network
    Route::delete('/album/{id}', 'destroy'); // Delete a specific social network
});

// Evento
Route::controller(EventoController::class)->group(function () {
    Route::get('/eventos', 'index'); // List all events
    Route::post('/eventos', 'store'); // Create a new event
    Route::get('/eventos/{id}', 'show'); // Get a specific event
    Route::put('/eventos/{id}', 'update'); // Update a specific event
    Route::delete('/eventos/{id}', 'destroy'); // Delete a specific event
});

// Noticia
Route::controller(NoticiaController::class)->group(function () {
    Route::get('/noticias', 'index'); // List all news
    Route::post('/noticias', 'store'); // Create a new news
    Route::get('/noticias/{id}', 'show'); // Get a specific news
    Route::put('/noticias/{id}', 'update'); // Update a specific news
    Route::delete('/noticias/{id}', 'destroy'); // Delete a specific news
});

// Comentario
Route::controller(ComentarioController::class)->group(function () {
    Route::get('/comentarios', 'index'); // List all news
    Route::post('/comentarios', 'store'); // Create a new news
    Route::get('/comentarios/{id}', 'show'); // Get a specific news
    Route::put('/comentarios/{id}', 'update'); // Update a specific news
    Route::delete('/comentarios/{id}', 'destroy'); // Delete a specific news
});

// Accion (like, dislike, reportar)
Route::controller(AccionController::class)->group(function () {
    Route::get('/acciones', 'index'); // List all news
    Route::post('/acciones', 'store'); // Create a new news
    Route::get('/acciones/{id}', 'show'); // Get a specific news
    Route::put('/acciones/{id}', 'update'); // Update a specific news
    Route::delete('/acciones/{id}', 'destroy'); // Delete a specific news
});

//Productos
Route::controller(ProductoController::class)->group(function () {
    Route::get('/productos', 'index'); // List all news
    Route::post('/productos', 'store'); // Create a new news
    Route::get('/productos/{id}', 'show'); // Get a specific news
    Route::put('/productos/{id}', 'update'); // Update a specific news
    Route::delete('/productos/{id}', 'destroy'); // Delete a specific news
});

// Comprobante
Route::controller(ComprobanteController::class)->group(function () {
    Route::get('/comprobantes', 'index'); // List all comprobantes
    Route::post('/comprobantes', 'store'); // Create a new comprobante
    Route::get('/comprobantes/{id}', 'show'); // Get a specific comprobante
    Route::put('/comprobantes/{id}', 'update'); // Update a specific comprobante
    Route::delete('/comprobantes/{id}', 'destroy'); // Delete a specific comprobante
});

// Notificacion
Route::controller(NotificacionController::class)->group(function () {
    Route::get('/notificaciones', 'index'); // List all notificaciones
    Route::post('/notificaciones', 'store'); // Create a new comprobante
    Route::get('/notificaciones/{id}', 'show'); // Get a specific comprobante
    Route::put('/notificaciones/{id}', 'update'); // Update a specific comprobante
    Route::delete('/notificaciones/{id}', 'destroy'); // Delete a specific comprobante
});

// Redes Sociales
Route::controller(RedSocialController::class)->group(function () {
    Route::get('/redes-sociales', 'index'); // List all social networks
    Route::post('/redes-sociales', 'store'); // Create a new social network
    Route::get('/redes-sociales/{id}', 'show'); // Get a specific social network
    Route::put('/redes-sociales/{id}', 'update'); // Update a specific social network
    Route::delete('/redes-sociales/{id}', 'destroy'); // Delete a specific social network
});
