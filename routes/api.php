<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|*/
Route::controller(RolController::class)->group(function () {
    Route::get('/roles', 'index'); // List all roles
    Route::post('/roles', 'store'); // Create a new role
    Route::get('/roles/{id}', 'show'); // Get a specific role
    Route::put('/roles/{id}', 'update'); // Update a specific role
    Route::delete('/roles/{id}', 'destroy'); // Delete a specific role
});