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
    Route::post('/comprobantes/desde-mercadopago', [ComprobanteController::class, 'crearDesdePagoMP']);
});
