<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoffeController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Route::prefix() digunakan untuk menambahkan awalan (prefix) pada URL semua rute yang didefinisikan di dalam grup tersebut.
// Catatan: Mendefinisikan group route dapat oleh pengguna yang belum terotentikasi.
Route::middleware('guest')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
    });
    Route::prefix('/coffe')->group(function () {
        Route::get('/', [CoffeController::class, 'get']);
    });
    Route::prefix('/order')->group(function () {
        Route::post('/', [OrderController::class, 'create']);
    });
});


// Catatan: Mendefinisikan group route hanya dapat diakses oleh pengguna dengan otentikasi.
//middleware menggunakan jwt middleware
Route::middleware(['jwt.verify', 'auth:api'])->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
    });
    Route::prefix('/coffe')->group(function () {
        Route::post('/', [CoffeController::class, 'create']);
        Route::put('/{id}', [CoffeController::class, 'update']);
        Route::delete('/{id}', [CoffeController::class, 'delete']);
    });
    Route::prefix('/order')->group(function () {
        Route::get('/', [OrderController::class, 'get']);
    });
});

