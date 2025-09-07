<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\RedSocialController;
use App\Http\Controllers\EventoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|*/

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

/////////
// Redes Sociales
Route::controller(RedSocialController::class)->group(function () {
    Route::get('/redes-sociales', 'index'); // List all social networks
    Route::post('/redes-sociales', 'store'); // Create a new social network
    Route::get('/redes-sociales/{id}', 'show'); // Get a specific social network
    Route::put('/redes-sociales/{id}', 'update'); // Update a specific social network
    Route::delete('/redes-sociales/{id}', 'destroy'); // Delete a specific social network
});
