<?php

use App\Http\Controllers\API\JenisPengeluaranController;
use App\Http\Controllers\API\KategoriPendapatanController;
use App\Http\Controllers\API\KategoriPengeluaranController;
use App\Http\Controllers\API\KoreksiController;
use App\Http\Controllers\API\PajakRekanAktaController;
use App\Http\Controllers\API\PajakRekanController;
use App\Http\Controllers\API\PendapatanController;
use App\Http\Controllers\API\PengeluaranController;
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
    Route::get('pendapatan', [PendapatanController::class, 'read']);
    Route::post('update-pendapatan', [PendapatanController::class, 'update']);
    Route::post('delete-pendapatan', [PendapatanController::class, 'delete']);

    // Kategori Pengeluaran
    Route::post('kategori-pengeluaran', [KategoriPengeluaranController::class, 'create']);
    Route::get('kategori-pengeluaran', [KategoriPengeluaranController::class, 'read']);

    // Jenis Pengeluaran
    Route::post('jenis-pengeluaran', [JenisPengeluaranController::class, 'create']);
    Route::get('jenis-pengeluaran', [JenisPengeluaranController::class, 'read']);

    // Pengeluaran
    Route::post('pengeluaran', [PengeluaranController::class, 'create']);
    Route::get('pengeluaran', [PengeluaranController::class, 'read']);
    Route::post('update-pengeluaran', [PengeluaranController::class, 'update']);
    Route::post('delete-pengeluaran', [PengeluaranController::class, 'delete']);

    // Pajak Rekan
    Route::post('pajak-rekan', [PajakRekanController::class, 'create']);
    Route::get('pajak-rekan', [PajakRekanController::class, 'read']);
    Route::post('update-pajak-rekan', [PajakRekanController::class, 'update']);
    Route::post('delete-pajak-rekan', [PajakRekanController::class, 'delete']);

    // Pajak Rekan Akta
    Route::post('pajak-rekan-akta', [PajakRekanAktaController::class, 'create']);
    Route::get('pajak-rekan-akta', [PajakRekanAktaController::class, 'read']);
    Route::post('update-pajak-rekan-akta', [PajakRekanAktaController::class, 'update']);
    Route::post('delete-pajak-rekan-akta', [PajakRekanAktaController::class, 'delete']);

    // Koreksi
    Route::post('koreksi', [KoreksiController::class, 'create']);
    Route::get('koreksi', [KoreksiController::class, 'read']);
    Route::post('update-koreksi', [KoreksiController::class, 'update']);
    Route::post('delete-koreksi', [KoreksiController::class, 'delete']);
});
