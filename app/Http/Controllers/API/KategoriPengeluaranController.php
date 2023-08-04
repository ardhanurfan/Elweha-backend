<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\KategoriPengeluaran;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KategoriPengeluaranController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|unique:kategori_pengeluaran,nama',
            ]);

            $kategori_pengeluaran = KategoriPengeluaran::create([
                'nama' => $request->nama,
            ]);

            return ResponseFormatter::success(
                $kategori_pengeluaran,
                'Create Kategori Pengeluaran Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Kategori Pengeluaran Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Kategori Pengeluaran Failed',
                500,
            );
        }
    }

    // READ
    public function read(Request $request)
    {
        $kategori_pengeluaran = KategoriPengeluaran::all();

        return ResponseFormatter::success(
            $kategori_pengeluaran,
            'Get Kategori Pengeluaran Successfully'
        );
    }

    // UPDATE
    public function update(Request $request)
    {
    }

    // DELETE
    public function delete(Request $request)
    {
    }
}
