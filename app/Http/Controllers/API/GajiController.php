<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Gaji;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


class GajiController extends Controller
{
    //
    public function create(Request $request)
    {
        try {
            $request->validate([
                'nama_karyawan' => 'required',
                'jenis_gaji' => 'required',
                'jumlah_gaji' => 'required|integer',
            ]);

            $gaji = Gaji::create([
                'user_id' => Auth::id(),
                'nama_karyawan' => $request->nama_karyawan,
                'jenis_gaji' => $request->jenis_gaji,
                'jumlah_gaji' => $request->jumlah_gaji,
                
            ]);

            return ResponseFormatter::success(
                $gaji,
                'Create Gaji Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Gaji Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Gaji Failed',
                500,
            );
        }
    }

    // READ
    public function read(Request $request)
    {
        $user_id = $request->input('user_id');
        $limit = $request->input('limit');
        $search = $request->input('search');

        $gaji = Gaji::with(['user']);

        if ($user_id) {
            $gaji->where('user_id', $user_id);
        }

        if ($search) {
            $gaji->select("gaji.*")
                ->where(function ($query) use ($search) {
                    return $query
                        ->orWhere('nama_karyawan', 'like', '%' . $search . '%')
                        ->orWhere('kehadiran', 'like', '%' . $search . '%')
                        ->orWhere('jenis_gaji', 'like', '%' . $search . '%')
                        ->orWhere('jumlah_gaji', 'like', '%' . $search . '%')
                        ->orWhere('jumlah_bonus', 'like', '%' . $search . '%')
                        ->orWhere('pph_dipotong', 'like', '%' . $search . '%')
                        ->orWhere('pajak_akumulasi', 'like', '%' . $search . '%')
                        ->orWhere('transfer', 'like', '%' . $search . '%');
                });
        }

        $total = Gaji::sum('transfer');

        return ResponseFormatter::success(
            [
                'total_gaji' => $total,
                'table' => $gaji->paginate($limit)
            ],
            'Get Gaji Successfully'
        );
    }

    //UPDATE
    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'nama_karyawan' => 'required',
                'kehadiran' => 'required|integer',
                'jenis_gaji' => 'required',
                'jumlah_gaji' => 'required|integer',
                'jumlah_bonus' => 'required|integer',
                'pph_dipotong' => 'required|integer',
                'pajak_akumulasi' => 'required|integer',
                'transfer' => 'required|integer',
            ]);

            $gaji = Gaji::find($request->id);

            if (!$gaji) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Data not found',
                    ],
                    'Edit Gaji Failed',
                    404,);
            }

            $gaji->update([
                'user_id' => Auth::id(),
                'nama_karyawan' => $request->nama_karyawan,
                'kehadiran' => $request->kehadiran,
                'jenis_gaji' => $request->jenis_gaji,
                'jumlah_gaji' => $request->jumlah_gaji,
                'jumlah_bonus' => $request->jumlah_bonus,
                'pph_dipotong' => $request->pph_dipotong,
                'pajak_akumulasi' => $request->pajak_akumulasi,
                'transfer' => $request->transfer,
            ]);

            return ResponseFormatter::success(
                $gaji,
                'Edit Gaji Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Edit Gaji Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Edit Gaji Failed',
                500,
            );
        }
    }

    // DELETE
    public function delete(Request $request)
    {
        try {
            $request->validate([
                'selectedId' => 'required|array',
            ]);

            foreach ($request->selectedId as $id) {
                $gaji = Gaji::find($id);

                if (!$gaji) {
                    return ResponseFormatter::error(
                        null,
                        'Some Data Not Found',
                        404
                    );
                }

                $gaji->forceDelete();
            }

            return ResponseFormatter::success(
                null,
                'Delete Gaji Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Gaji Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Gaji Failed',
                500,
            );
        }
    }
}
