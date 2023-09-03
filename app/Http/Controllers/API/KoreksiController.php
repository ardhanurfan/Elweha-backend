<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Koreksi;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class KoreksiController extends Controller
{
    public function create(request $request)
    {
        try {
            $request->validate([
                'sifat_koreksi' => 'required | in:POSITIF,NEGATIF',
                'jenis_koreksi' => 'required',
                'jumlah' => 'required',
                'tahun' => 'required',
            ]);
            $koreksi = Koreksi::create([
                'user_id' => Auth::id(),
                'sifat_koreksi' => $request->sifat_koreksi,
                'jenis_koreksi' => $request->jenis_koreksi,
                'jumlah' => $request->jumlah,
                'tahun' => $request->tahun,
            ]);
            return ResponseFormatter::success(
                $koreksi,
                'Create Koreksi Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Koreksi Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Koreksi Failed',
                500,
            );
        }
    }

    public function read(request $request)
    {
        $tahun = $request->input('tahun');
        $koreksi = Koreksi::query();
        if ($request->sifat_koreksi) {
            $koreksi->where('sifat_koreksi', $request->sifat_koreksi);
        }

        if ($tahun) {
            $koreksi->whereYear('tahun', $tahun);
        }

        $total = $koreksi->sum('jumlah');
        return ResponseFormatter::success(
            [
                'total_koreksi' => $total,
                'table' => $koreksi->get(),
            ],
            'Get Koreksi Successfully'
        );
    }

    public function update(request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'sifat_koreksi' => 'in:POSITIF,NEGATIF',
            ]);

            $koreksi = Koreksi::Find($request->id);

            if (!$koreksi) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Data Not Found',
                    ],
                    'Edit Koreksi Failed',
                    404,
                );
            }

            $koreksi->update([
                'user_id' => Auth::id(),
                'sifat_koreksi' => $request->sifat_koreksi,
                'jenis_koreksi' => $request->jenis_koreksi,
                'jumlah' => $request->jumlah,
            ]);
            return ResponseFormatter::success(
                $koreksi,
                'Edit Koreksi Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Edit Koreksi Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Edit Koreksi Failed',
                500,
            );
        }
    }

    public function delete(request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);

            $koreksi = Koreksi::find($request->id);
            if (!$koreksi) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Data Not Found',
                    ],
                    'Delete Koreksi Failed',
                    404,
                );
            }

            $koreksi->forceDelete();

            return ResponseFormatter::success(
                null,
                'Delete Koreksi Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Koreksi Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Koreksi Failed',
                500,
            );
        }
    }
}
