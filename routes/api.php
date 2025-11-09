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
// Rutas públicas
// -------------------
Route::post('/registro', [AuthController::class, 'registro']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/forgotten', [AuthController::class, 'forgotten']);
Route::post('/reset-password', [AuthController::class, 'reset']);

// Imágenes (podrías proteger destroy si querés)
Route::post('/imagenes', [ImageController::class, 'store']);
Route::delete('/imagenes', [ImageController::class, 'destroy']);

// ---------- GETs públicos ---------- //
// Álbum
Route::get('/album', [AlbumController::class, 'index']);
Route::get('/album/{id}', [AlbumController::class, 'show']);

// Eventos
Route::get('/eventos', [EventoController::class, 'index']);
Route::get('/eventos/{id}', [EventoController::class, 'show']);

// Noticias
Route::get('/noticias', [NoticiaController::class, 'index']);
Route::get('/noticias/{id}', [NoticiaController::class, 'show']);

// Productos
Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);

// Redes Sociales
Route::get('/redes-sociales', [RedSocialController::class, 'index']);
Route::get('/redes-sociales/{id}', [RedSocialController::class, 'show']);

// -------------------
// Rutas protegidas
// -------------------
Route::middleware('mongo.auth')->group(function () {
    // Sesión y usuario actual
    Route::post('/cerrarsesion', [AuthController::class, 'cerrarsesion']);
    Route::get('/usuario', fn(Request $request) => $request->user());

    // Notificaciones
    Route::controller(NotificacionController::class)->group(function () {
        Route::get('/notificaciones', 'index');
        Route::post('/notificaciones', 'store');
        Route::get('/notificaciones/{id}', 'show');
        Route::put('/notificaciones/{id}', 'update');
        Route::delete('/notificaciones/{id}', 'destroy');
        Route::patch('/notificaciones/{id}/marcar-leida', 'markAsRead');
        Route::post('/notificaciones/marcar-todas-leidas', 'markAllAsRead');
    });

    // Roles
    Route::controller(RolController::class)->group(function () {
        Route::get('/roles', 'index');
        Route::post('/roles', 'store');
        Route::get('/roles/{id}', 'show');
        Route::put('/roles/{id}', 'update');
        Route::delete('/roles/{id}', 'destroy');
    });

    // Usuarios
    Route::controller(UsuarioController::class)->group(function () {
        Route::get('/usuarios', 'index');
        Route::get('/usuarios/{id}', 'show');
        Route::put('/usuarios/{id}', 'update');
        Route::delete('/usuarios/{id}', 'destroy');
    });

    // Auditoría
    Route::controller(AuditoriaController::class)->group(function () {
        Route::get('/auditoria', 'index');
        Route::post('/auditoria', 'store');
        Route::get('/auditoria/{id}', 'show');
        Route::put('/auditoria/{id}', 'update');
        Route::delete('/auditoria/{id}', 'destroy');
    });

    // Álbum
    Route::controller(AlbumController::class)->group(function () {
        Route::post('/album', 'store');
        Route::put('/album/{id}', 'update');
        Route::delete('/album/{id}', 'destroy');
    });

    // Eventos
    Route::controller(EventoController::class)->group(function () {
        Route::post('/eventos', 'store');
        Route::post('/eventos/{nombreEvento}/imagen', 'subirImagen');
        Route::put('/eventos/{nombreEvento}', 'update');
        Route::delete('/eventos/{nombreEvento}', 'destroy');
        Route::post('/eventos/{nombreEvento}/solicitudCompra', 'generarSolicitudCompra');
        Route::post('/eventos/{nombreEvento}/compra', 'generarCompra');
    });

    // Noticias
    Route::controller(NoticiaController::class)->group(function () {
        Route::post('/noticias', 'store');
        Route::put('/noticias/{id}', 'update');
        Route::delete('/noticias/{id}', 'destroy');
    });

    // Comentarios
    Route::controller(ComentarioController::class)->group(function () {
        Route::post('/comentarios', 'store');
        Route::put('/comentarios/{id}', 'update');
        Route::delete('/comentarios/{id}', 'destroy');
        Route::post('/noticias/{noticia}/comentarios', 'storeForNoticia');
    });

    // Reacciones
    Route::controller(ReaccionController::class)->group(function () {
        Route::post('/reacciones', 'store'); // Toggle o registra una reaccion
        Route::get('/noticias/{noticia}/reacciones', 'summaryForNoticia'); // Resumen de reacciones de una noticia
        Route::get('/comentarios/{comentario}/reacciones', 'summaryForComentario'); // Resumen de reacciones de un comentario
    });

    // Acciones
    Route::controller(AccionController::class)->group(function () {
        Route::post('/acciones', 'store');
        Route::put('/acciones/{id}', 'update');
        Route::delete('/acciones/{id}', 'destroy');
    });

    // Productos
    Route::controller(ProductoController::class)->group(function () {
        Route::post('/productos', 'store');
        Route::put('/productos/{id}', 'update');
        Route::delete('/productos/{id}', 'destroy');
    });

    // Comprobantes
    Route::controller(ComprobanteController::class)->group(function () {
        Route::post('/comprobantes', 'store');
        Route::put('/comprobantes/{id}', 'update');
        Route::delete('/comprobantes/{id}', 'destroy');
    });

    // Redes Sociales
    Route::controller(RedSocialController::class)->group(function () {
        Route::post('/redes-sociales', 'store');
        Route::put('/redes-sociales/{id}', 'update');
        Route::delete('/redes-sociales/{id}', 'destroy');
    });

    // Mercado Pago
    Route::post('/preferencias', [PreferenciaMP::class, 'crearPreferencia']);
});
