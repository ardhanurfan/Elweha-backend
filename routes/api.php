<?php

use App\Http\Controllers\API\BarangController;
use App\Http\Controllers\API\GajiController;
use App\Http\Controllers\API\JenisBarangController;
use App\Http\Controllers\API\JenisPengeluaranController;
use App\Http\Controllers\API\KategoriPendapatanController;
use App\Http\Controllers\API\KategoriPengeluaranController;
use App\Http\Controllers\API\KehadiranController;
use App\Http\Controllers\API\KoreksiController;
use App\Http\Controllers\API\PajakRekanAktaController;
use App\Http\Controllers\API\PajakRekanController;
use App\Http\Controllers\API\PendapatanController;
use App\Http\Controllers\API\PengambilBarangController;
use App\Http\Controllers\API\PengeluaranController;
use App\Http\Controllers\API\RekanController;
use App\Http\Controllers\API\SkilBonusController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\VariabelBonusController;
use App\Models\Rekan;
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
    Route::get('daftar-user', [UserController::class, 'read']);
    Route::post('edit-account', [UserController::class, 'edit']);
    Route::post('delete-account', [UserController::class, 'delete']);
    Route::post('logout', [UserController::class, 'logout']);

    // Kategori Pendapatan
    Route::post('kategori-pendapatan', [KategoriPendapatanController::class, 'create']);
    Route::get('kategori-pendapatan', [KategoriPendapatanController::class, 'read']);
    Route::post('delete-kategori-pendapatan', [KategoriPendapatanController::class, 'delete']);
    Route::post('update-kategori-pendapatan', [KategoriPendapatanController::class, 'update']);

    // Pendapatan
    Route::post('pendapatan', [PendapatanController::class, 'create']);
    Route::get('pendapatan', [PendapatanController::class, 'read']);
    Route::post('update-pendapatan', [PendapatanController::class, 'update']);
    Route::post('delete-pendapatan', [PendapatanController::class, 'delete']);

    // Kategori Pengeluaran
    Route::post('kategori-pengeluaran', [KategoriPengeluaranController::class, 'create']);
    Route::get('kategori-pengeluaran', [KategoriPengeluaranController::class, 'read']);
    Route::post('delete-kategori-pengeluaran', [KategoriPengeluaranController::class, 'delete']);
    Route::post('update-kategori-pengeluaran', [KategoriPengeluaranController::class, 'update']);

    // Jenis Pengeluaran
    Route::post('jenis-pengeluaran', [JenisPengeluaranController::class, 'create']);
    Route::get('jenis-pengeluaran', [JenisPengeluaranController::class, 'read']);
    Route::post('delete-jenis-pengeluaran', [JenisPengeluaranController::class, 'delete']);
    Route::post('update-jenis-pengeluaran', [JenisPengeluaranController::class, 'update']);

    // Pengeluaran
    Route::post('pengeluaran', [PengeluaranController::class, 'create']);
    Route::post('pengeluaran-gaji', [PengeluaranController::class, 'createLinkGaji']);
    Route::get('pengeluaran', [PengeluaranController::class, 'read']);
    Route::post('update-pengeluaran', [PengeluaranController::class, 'update']);
    Route::post('delete-pengeluaran', [PengeluaranController::class, 'delete']);

    // Rekan
    Route::post('rekan', [RekanController::class, 'create']);
    Route::get('rekan', [RekanController::class, 'read']);
    Route::post('update-rekan', [RekanController::class, 'update']);
    Route::post('delete-rekan', [RekanController::class, 'delete']);

    // Pajak Rekan
    Route::get('pajak-rekan-by-id', [PajakRekanController::class, 'readByRekanId']);
    Route::get('pajak-rekan-by-tahun', [PajakRekanController::class, 'readByTahun']);

    // Pajak Rekan Akta
    Route::post('pajak-rekan-akta', [PajakRekanAktaController::class, 'create']);
    Route::get('pajak-rekan-akta', [PajakRekanAktaController::class, 'read']);
    Route::post('update-pajak-rekan-akta', [PajakRekanAktaController::class, 'update']);
    Route::post('delete-pajak-rekan-akta', [PajakRekanAktaController::class, 'delete']);
    Route::get('akta-tersisa', [PajakRekanAktaController::class, 'getAktaTersisa']);

    // Koreksi
    Route::post('koreksi', [KoreksiController::class, 'create']);
    Route::get('koreksi', [KoreksiController::class, 'read']);
    Route::post('update-koreksi', [KoreksiController::class, 'update']);
    Route::post('delete-koreksi', [KoreksiController::class, 'delete']);

    // Gaji
    Route::post('gaji', [GajiController::class, 'create']);
    Route::get('gaji', [GajiController::class, 'read']);
    Route::post('update-gaji', [GajiController::class, 'update']);
    Route::post('delete-gaji', [GajiController::class, 'delete']);

    // Kehadiran
    Route::post('kehadiran', [KehadiranController::class, 'create']);

    // Skil Bonus
    Route::post('skil-bonus', [SkilBonusController::class, 'create']);
    Route::post('delete-skil-bonus', [SkilBonusController::class, 'delete']);

    // Variabel Bonus
    Route::post('variabel-bonus', [VariabelBonusController::class, 'create']);
    Route::post('delete-variabel-bonus', [VariabelBonusController::class, 'delete']);

    // Jenis Barang
    Route::post('jenis-barang', [JenisBarangController::class, 'create']);
    Route::get('jenis-barang', [JenisBarangController::class, 'read']);
    Route::post('delete-jenis-barang', [JenisBarangController::class, 'delete']);
    Route::post('update-jenis-barang', [JenisBarangController::class, 'update']);

    // Barang
    Route::post('barang', [BarangController::class, 'create']);
    Route::get('barang', [BarangController::class, 'read']);
    Route::post('update-barang', [BarangController::class, 'update']);
    Route::post('delete-barang', [BarangController::class, 'delete']);

    // Pengambilan Barang
    Route::post('pengambilan', [PengambilBarangController::class, 'create']);
    Route::get('pengambilan', [PengambilBarangController::class, 'read']);
    Route::post('update-pengambilan', [PengambilBarangController::class, 'update']);
    Route::post('delete-pengambilan', [PengambilBarangController::class, 'delete']);
});
