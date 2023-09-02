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
        try {
            $request->validate([
                'id' => 'required',
                'nama' => 'required|unique:jenis_pengeluaran,nama,' . $request->id,
            ]);

            $jenis_pengeluaran = JenisPengeluaran::find($request->id);

            if (!$jenis_pengeluaran) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => "Data Not Found",
                    ],
                    'Delete Jenis Pengeluaran Failed',
                    404,
                );
            }

            $jenis_pengeluaran->update([
                'nama' => $request->nama,
            ]);

            return ResponseFormatter::success(
                $jenis_pengeluaran,
                'Update Jenis Pengeluaran Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Update Jenis Pengeluaran Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Update Jenis Pengeluaran Failed',
                500,
            );
        }
    }

    // DELETE
    public function delete(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);

            $jenis_pengeluaran = JenisPengeluaran::find($request->id);

            if (!$jenis_pengeluaran) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => "Data Not Found",
                    ],
                    'Delete Jenis Pengeluaran Failed',
                    404,
                );
            }

            $jenis_pengeluaran->forceDelete();

            return ResponseFormatter::success(
                null,
                'Delete Jenis Pengeluaran Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Jenis Pengeluaran Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Jenis Pengeluaran Failed',
                500,
            );
        }
    }
}
