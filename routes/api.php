<?php

use Illuminate\Http\Request;

use App\Http\Controllers\AccionController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\RedSocialController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PreferenciaMP;
use App\Http\Controllers\ReaccionController;
use MercadoPago\Client\Preference\PreferenceClient;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|*/
// -------------------
// Rutas públicas (sin middleware)
// -------------------
Route::post('/registro', [AuthController::class, 'registro']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/forgotten', [AuthController::class, 'forgotten']);
Route::post('/reset-password', [AuthController::class, 'reset']);

// Visualización pública (GET)
Route::get('/album', [AlbumController::class, 'index']);
Route::get('/album/{id}', [AlbumController::class, 'show']);
Route::get('/eventos', [EventoController::class, 'index']);
Route::get('/eventos/{id}', [EventoController::class, 'show']);
Route::get('/noticias', [NoticiaController::class, 'index']);
Route::get('/noticias/{id}', [NoticiaController::class, 'show']);
Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);
Route::get('/redes-sociales', [RedSocialController::class, 'index']);
Route::get('/redes-sociales/{id}', [RedSocialController::class, 'show']);
Route::post('/imagenes', [ImageController::class, 'store']);
Route::delete('/imagenes', [ImageController::class, 'destroy']);

// -------------------
// Rutas protegidas (con token válido)
// -------------------
Route::middleware('mongo.auth')->group(function () {
    // Sesión y usuario actual
    Route::post('/cerrarsesion', [AuthController::class, 'cerrarsesion']);
    Route::get('/usuario', fn(Request $request) => $request->user());

    // Gestión interna
    Route::apiResource('usuarios', UsuarioController::class)->except(['store']);
    Route::apiResource('roles', RolController::class);
    Route::apiResource('auditoria', AuditoriaController::class)->except(['show']);

    // Eventos y entradas (tickets)
    Route::controller(EventoController::class)->group(function () {
        Route::post('/eventos', 'store');
        Route::put('/eventos/{nombreEvento}', 'update');
        Route::delete('/eventos/{nombreEvento}', 'destroy');
        Route::post('/eventos/{nombreEvento}/imagen', 'subirImagen');
        Route::post('/eventos/{nombreEvento}/solicitudCompra', 'generarSolicitudCompra');
        Route::post('/eventos/{nombreEvento}/compra', 'generarCompra');
    });

    // Noticias y comentarios
    Route::controller(NoticiaController::class)->group(function () {
        Route::post('/noticias', 'store');
        Route::put('/noticias/{id}', 'update');
        Route::delete('/noticias/{id}', 'destroy');
    });

    Route::controller(ComentarioController::class)->group(function () {
        Route::post('/comentarios', 'store');
        Route::put('/comentarios/{id}', 'update');
        Route::delete('/comentarios/{id}', 'destroy');
        Route::post('/noticias/{noticia}/comentarios', 'storeForNoticia');
    });

    // Reacciones
    Route::controller(ReaccionController::class)->group(function () {
        Route::post('/reacciones', 'store');
        Route::get('/noticias/{noticia}/reacciones', 'summaryForNoticia');
        Route::get('/comentarios/{comentario}/reacciones', 'summaryForComentario');
    });

    // Merch / productos
    Route::controller(ProductoController::class)->group(function () {
        Route::post('/productos', 'store');
        Route::put('/productos/{id}', 'update');
        Route::delete('/productos/{id}', 'destroy');
    });

    // Comprobantes (facturas, compras)
    Route::apiResource('comprobantes', ComprobanteController::class)->except(['index', 'show']);

    // Acciones / auditoría / redes
    Route::apiResource('acciones', AccionController::class)->except(['index', 'show']);
    Route::apiResource('redes-sociales', RedSocialController::class)->except(['index', 'show']);

    // Mercado Pago
    Route::post('/preferencias', [PreferenciaMP::class, 'crearPreferencia']);
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
    Route::post('/eventos/{nombreEvento}/imagen', 'subirImagen'); // Upload image for an event
    Route::put('/eventos/{nombreEvento}', 'update'); // Update a specific event
    Route::delete('/eventos/{nombreEvento}', 'destroy'); // Delete a specific event


});

// Comprar entradas para un evento -- sin proba
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/eventos/{nombreEvento}/solicitudCompra', [EventoController::class, 'generarSolicitudCompra']);
    Route::post('/eventos/{nombreEvento}/compra', [EventoController::class, 'generarCompra']);
});

// Accion (like, dislike, reportar)
Route::controller(AccionController::class)->group(function () {
    Route::get('/acciones', 'index'); // List all news
    Route::post('/acciones', 'store'); // Create a new news
    Route::get('/acciones/{id}', 'show'); // Get a specific news
    Route::put('/acciones/{id}', 'update'); // Update a specific news
    Route::delete('/acciones/{id}', 'destroy'); // Delete a specific news
});

// Comprobante
Route::controller(ComprobanteController::class)->group(function () {
    Route::get('/comprobantes', 'index'); // List all comprobantes
    Route::post('/comprobantes', 'store'); // Create a new comprobante
    Route::get('/comprobantes/{id}', 'show'); // Get a specific comprobante
    Route::put('/comprobantes/{id}', 'update'); // Update a specific comprobante
    Route::delete('/comprobantes/{id}', 'destroy'); // Delete a specific comprobante
});

// Redes Sociales
Route::controller(RedSocialController::class)->group(function () {
    Route::get('/redes-sociales', 'index'); // List all social networks
    Route::post('/redes-sociales', 'store'); // Create a new social network
    Route::get('/redes-sociales/{id}', 'show'); // Get a specific social network
    Route::put('/redes-sociales/{id}', 'update'); // Update a specific social network
    Route::delete('/redes-sociales/{id}', 'destroy'); // Delete a specific social network
});

// MP
Route::post('/preferencias', [PreferenciaMP::class, 'crearPreferencia']);
Route::post('/comprobantes/desde-mercadopago', [ComprobanteController::class, 'crearDesdePagoMP']);
