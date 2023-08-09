<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\PajakRekanAkta;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PajakRekanAktaController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $request->validate([
                'pajak_rekan_id' => 'required|integer',
                'tanggal' => 'required|date',
                'no_awal' => 'required|integer|min:1',
                'no_akhir' => 'required|integer|min:' . $request->no_awal,
            ]);

            // Cek Ketersediaan Nomor Akta
            $sold = [];
            $get_no = PajakRekanAkta::select('no_awal', 'no_akhir')->get();
            foreach ($get_no as $row) {
                for ($i = $row->no_awal; $i <= $row->no_akhir; $i++) {
                    array_push($sold, $i);
                }
            }

            $i = $request->no_awal;
            while ($i <= $request->no_akhir) {
                if (in_array($i, $sold)) {
                    return ResponseFormatter::error(
                        [
                            'message' => 'Something when wrong',
                            'error' => 'Akta number not available',
                        ],
                        'Create Akta Failed',
                        404,
                    );
                }
                $i++;
            }

            $pajak_rekan_akta = PajakRekanAkta::create([
                'user_id' => Auth::id(),
                'pajak_rekan_id' => $request->pajak_rekan_id,
                'tanggal' => $request->tanggal,
                'no_awal' => $request->no_awal,
                'no_akhir' => $request->no_akhir,
                'jumlah_akta' => $request->no_akhir - $request->no_awal + 1,
            ]);

            return ResponseFormatter::success(
                $pajak_rekan_akta,
                'Create Akta Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Akta Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Akta Failed',
                500,
            );
        }
    }

    // READ
    public function read(Request $request)
    {
        $user_id = $request->input('user_id');
        $limit = $request->input('limit');
        $pajak_rekan_id = $request->input('pajak_rekan_id');

        $pajak_rekan_akta = PajakRekanAkta::with(['user'])->where('pajak_rekan_id', $pajak_rekan_id);

        if ($user_id) {
            $pajak_rekan_akta->where('user_id', $user_id);
        }

        return ResponseFormatter::success(
            [
                'table' => $pajak_rekan_akta->orderBy('tanggal', 'DESC')->paginate($limit)
            ],
            'Get Akta Successfully'
        );
    }

    // UPDATE
    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'pajak_rekan_id' => 'required|integer',
                'tanggal' => 'required|date',
                'no_awal' => 'required|integer|min:1',
                'no_akhir' => 'required|integer|min:' . $request->no_awal,
            ]);

            $pajak_rekan_akta = PajakRekanAkta::find($request->id);

            if (!$pajak_rekan_akta) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Data not found',
                    ],
                    'Edit Akta Failed',
                    404,
                );
            }

            // Cek Ketersediaan Nomor Akta, kecuali diirinya sendiri
            $sold = [];
            $get_no = PajakRekanAkta::select('no_awal', 'no_akhir')->where('id', '!=', $request->id)->get();
            foreach ($get_no as $row) {
                for ($i = $row->no_awal; $i <= $row->no_akhir; $i++) {
                    array_push($sold, $i);
                }
            }

            $i = $request->no_awal;
            while ($i <= $request->no_akhir) {
                if (in_array($i, $sold)) {
                    return ResponseFormatter::error(
                        [
                            'message' => 'Something when wrong',
                            'error' => 'Akta number not available',
                        ],
                        'Create Akta Failed',
                        404,
                    );
                }
                $i++;
            }

            $pajak_rekan_akta->update([
                'user_id' => Auth::id(),
                'pajak_rekan_id' => $request->pajak_rekan_id,
                'tanggal' => $request->tanggal,
                'no_awal' => $request->no_awal,
                'no_akhir' => $request->no_akhir,
                'jumlah_akta' => $request->no_akhir - $request->no_awal + 1,
            ]);

            return ResponseFormatter::success(
                $pajak_rekan_akta,
                'Edit Akta Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Edit Akta Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Edit Akta Failed',
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
                $pajak_rekan_akta = PajakRekanAkta::find($id);

                if (!$pajak_rekan_akta) {
                    return ResponseFormatter::error(
                        [
                            'message' => 'Something when wrong',
                            'error' => 'Some Data Not Found',
                        ],
                        'Delete Akta Failed',
                        404,
                    );
                }

                $pajak_rekan_akta->forceDelete();
            }

            return ResponseFormatter::success(
                null,
                'Delete Akta Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Akta Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Akta Failed',
                500,
            );
        }
    }
}
