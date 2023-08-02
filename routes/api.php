<?php

use App\Http\Controllers\API\KategoriPendapatanController;
use App\Http\Controllers\API\PendapatanController;
use App\Http\Controllers\API\UserController;
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

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'add']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('add-account', [UserController::class, 'add']);
    Route::get('user', [UserController::class, 'get']);

    // Kategori Pendapatan
    Route::post('kategori-pendapatan', [KategoriPendapatanController::class, 'create']);
    Route::get('kategori-pendapatan', [KategoriPendapatanController::class, 'read']);

    // Pendapatan
    Route::post('pendapatan', [PendapatanController::class, 'create']);
    Route::post('update-pendapatan', [PendapatanController::class, 'update']);
    Route::get('pendapatan', [PendapatanController::class, 'read']);
});
