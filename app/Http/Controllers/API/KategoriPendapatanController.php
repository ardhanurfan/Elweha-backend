<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\KategoriPendapatan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class KategoriPendapatanController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required',
            ]);

            $kategori_pendapatan = KategoriPendapatan::create([
                'nama' => $request->nama,
            ]);

            return ResponseFormatter::success(
                $kategori_pendapatan,
                'Create Kategori Pendapatan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Kategori Pendapatan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Kategori Pendapatan Failed',
                500,
            );
        }
    }

    // READ
    public function read(Request $request)
    {
        $kategori_pendapatan = KategoriPendapatan::all();

        return ResponseFormatter::success(
            $kategori_pendapatan,
            'Get Kategori Pendapatan Successfully'
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
