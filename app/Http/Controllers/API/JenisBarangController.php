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
        try {
            $request->validate([
                'id' => 'required',
                'nama' => 'required|unique:jenis_barang,nama,' . $request->id,
            ]);

            $jenis_barang = JenisBarang::find($request->id);

            if (!$jenis_barang) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => "Data Not Found",
                    ],
                    'Delete Jenis Barang Failed',
                    404,
                );
            }

            $jenis_barang->update([
                'nama' => $request->nama,
            ]);

            return ResponseFormatter::success(
                $jenis_barang,
                'Update Jenis Barang Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Update Jenis Barang Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Update Jenis Barang Failed',
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

            $jenis_barang = JenisBarang::find($request->id);

            if (!$jenis_barang) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => "Data Not Found",
                    ],
                    'Delete Jenis Barang Failed',
                    404,
                );
            }

            $jenis_barang->forceDelete();

            return ResponseFormatter::success(
                null,
                'Delete Jenis Barang Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Jenis Barang Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Jenis Barang Failed',
                500,
            );
        }
    }
}
