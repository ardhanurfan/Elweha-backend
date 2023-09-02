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
                'nama' => 'required|unique:kategori_pendapatan,nama',
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
        try {
            $request->validate([
                'id' => 'required',
                'nama' => 'required|unique:kategori_pendapatan,nama,' . $request->id,
            ]);

            $kategori_pendapatan = KategoriPendapatan::find($request->id);

            if (!$kategori_pendapatan) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => "Data Not Found",
                    ],
                    'Delete Kategori Pendapatan Failed',
                    404,
                );
            }

            $kategori_pendapatan->update([
                'nama' => $request->nama,
            ]);

            return ResponseFormatter::success(
                $kategori_pendapatan,
                'Update Kategori Pendapatan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Update Kategori Pendapatan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Update Kategori Pendapatan Failed',
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

            $kategori_pendapatan = KategoriPendapatan::find($request->id);

            if (!$kategori_pendapatan) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => "Data Not Found",
                    ],
                    'Delete Kategori Pendapatan Failed',
                    404,
                );
            }

            $kategori_pendapatan->forceDelete();

            return ResponseFormatter::success(
                null,
                'Delete Kategori Pendapatan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Kategori Pendapatan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Kategori Pendapatan Failed',
                500,
            );
        }
    }
}
