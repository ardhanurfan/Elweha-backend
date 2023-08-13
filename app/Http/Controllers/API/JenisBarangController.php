<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\JenisBarang;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JenisBarangController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|unique:jenis_barang,nama',
            ]);

            $jenis_barang = JenisBarang::create([
                'nama' => $request->nama,
            ]);

            return ResponseFormatter::success(
                $jenis_barang,
                'Create Jenis Barang Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Jenis Barang Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Jenis Barang Failed',
                500,
            );
        }
    }

    // READ
    public function read(Request $request)
    {
        $jenis_barang = JenisBarang::all();

        return ResponseFormatter::success(
            $jenis_barang,
            'Get Jenis Barang Successfully'
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
