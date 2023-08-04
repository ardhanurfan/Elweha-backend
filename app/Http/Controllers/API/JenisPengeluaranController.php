<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\JenisPengeluaran;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JenisPengeluaranController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|unique:jenis_pengeluaran,nama',
            ]);

            $jenis_pengeluaran = JenisPengeluaran::create([
                'nama' => $request->nama,
            ]);

            return ResponseFormatter::success(
                $jenis_pengeluaran,
                'Create Jenis Pengeluaran Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Jenis Pengeluaran Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Jenis Pengeluaran Failed',
                500,
            );
        }
    }

    // READ
    public function read(Request $request)
    {
        $jenis_pengeluaran = JenisPengeluaran::all();

        return ResponseFormatter::success(
            $jenis_pengeluaran,
            'Get Jenis Pengeluaran Successfully'
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
