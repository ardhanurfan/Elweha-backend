<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\PajakRekan;
use App\Models\PajakRekanAkta;
use App\Models\Rekan;
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
                'rekan_id' => 'required|integer',
                'tanggal' => 'required|date',
                'no_awal' => 'required|integer|min:1',
                'no_akhir' => 'required|integer|min:' . $request->no_awal,
            ]);

            // Cek Ketersediaan Nomor Akta
            $sold = [];
            $tahun = date('Y', strtotime($request->tanggal));
            $get_no = PajakRekanAkta::select('no_awal', 'no_akhir')->whereYear('tanggal', $tahun)->where('rekan_id', $request->rekan_id)->get();
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

            // hapus 00
            $nol =
                PajakRekan::where('rekan_id', $request->rekan_id)->where('bulan', 0)->where('tahun', 0)->first();
            if ($nol) {
                $nol->forceDelete();
            }

            $pajak_rekan_akta = PajakRekanAkta::create([
                'user_id' => Auth::id(),
                'rekan_id' => $request->rekan_id,
                'tanggal' => $request->tanggal,
                'no_awal' => $request->no_awal,
                'no_akhir' => $request->no_akhir,
                'jumlah_akta' => $request->no_akhir - $request->no_awal + 1,
            ]);

            $bulan = date('n', strtotime($request->tanggal));
            $tahun = date('Y', strtotime($request->tanggal));
            $pajak_rekan = PajakRekan::where('rekan_id', $request->rekan_id)->where('bulan', $bulan)->where('tahun', $tahun)->first();
            if ($pajak_rekan) {
                $jumlah_akta = $pajak_rekan->jumlah_akta + $pajak_rekan_akta->jumlah_akta;
                $pajak_rekan->update([
                    'user_id' => Auth::id(),
                    'jumlah_akta' => $jumlah_akta,
                ]);
            } else {
                $biaya_jasa = Rekan::where('id', $request->rekan_id)->first()->biaya_jasa;
                PajakRekan::create([
                    'user_id' => Auth::id(),
                    'rekan_id' => $request->rekan_id,
                    'biaya_jasa' => $biaya_jasa,
                    'jumlah_akta' => $pajak_rekan_akta->jumlah_akta,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]);
            }

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
        $rekan_id = $request->input('rekan_id');
        $year = $request->input('year');

        $pajak_rekan_akta = PajakRekanAkta::with(['user'])->where('rekan_id', $rekan_id);

        if ($year) {
            $pajak_rekan_akta->whereYear('tanggal', $year);
        }

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
                'rekan_id' => 'required|integer',
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
            $tahun = date('Y', strtotime($request->tanggal));
            $get_no = PajakRekanAkta::select('no_awal', 'no_akhir')->where('id', '!=', $request->id)->whereYear('tanggal', $tahun)->where('rekan_id', $request->rekan_id)->get();
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

            // Kurangi yang lama atau hapus jika kosong
            $bulan_old = date('n', strtotime($pajak_rekan_akta->tanggal));
            $tahun_old = date('Y', strtotime($pajak_rekan_akta->tanggal));
            $pajak_rekan_old = PajakRekan::where('rekan_id', $request->rekan_id)->where('bulan', $bulan_old)->where('tahun', $tahun_old)->first();
            if ($pajak_rekan_old) {
                $jumlah_akta = $pajak_rekan_old->jumlah_akta - $pajak_rekan_akta->jumlah_akta;
                if ($jumlah_akta == 0) {
                    $pajak_rekan_old->forceDelete();
                } else {
                    $pajak_rekan_old->update([
                        'user_id' => Auth::id(),
                        'jumlah_akta' => $jumlah_akta,
                    ]);
                }
            }

            $pajak_rekan_akta->update([
                'user_id' => Auth::id(),
                'rekan_id' => $request->rekan_id,
                'tanggal' => $request->tanggal,
                'no_awal' => $request->no_awal,
                'no_akhir' => $request->no_akhir,
                'jumlah_akta' => $request->no_akhir - $request->no_awal + 1,
            ]);


            // Update yang baru
            $bulan = date('n', strtotime($request->tanggal));
            $tahun = date('Y', strtotime($request->tanggal));
            $pajak_rekan = PajakRekan::where('rekan_id', $request->rekan_id)->where('bulan', $bulan)->where('tahun', $tahun)->first();
            if ($pajak_rekan) {
                $jumlah_akta = $pajak_rekan->jumlah_akta + $pajak_rekan_akta->jumlah_akta;
                $pajak_rekan->update([
                    'user_id' => Auth::id(),
                    'jumlah_akta' => $jumlah_akta,
                ]);
            } else {
                $biaya_jasa = Rekan::where('id', $request->rekan_id)->first()->biaya_jasa;
                PajakRekan::create([
                    'user_id' => Auth::id(),
                    'rekan_id' => $request->rekan_id,
                    'biaya_jasa' => $biaya_jasa,
                    'jumlah_akta' => $pajak_rekan_akta->jumlah_akta,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]);
            }

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
                'rekan_id' => 'required',
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

                $bulan = date('n', strtotime($pajak_rekan_akta->tanggal));
                $tahun = date('Y', strtotime($pajak_rekan_akta->tanggal));
                $pajak_rekan = PajakRekan::where('rekan_id', $request->rekan_id)->where('bulan', $bulan)->where('tahun', $tahun)->first();
                if ($pajak_rekan) {
                    $jumlah_akta = $pajak_rekan->jumlah_akta - $pajak_rekan_akta->jumlah_akta;
                    if ($jumlah_akta == 0) {
                        $pajak_rekan->forceDelete();
                    } else {
                        $pajak_rekan->update([
                            'user_id' => Auth::id(),
                            'jumlah_akta' => $jumlah_akta,
                        ]);
                    }
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

    //Akta Tersisa
    public function getAktaTersisa(request $request)
    {
        $rekan_id = $request->input('rekan_id');
        $year = $request->input('year');

        $akta = PajakRekanAkta::whereYear('tanggal', $year)->where('rekan_id', $rekan_id)->get();

        $aktaFill = [];
        foreach ($akta as $row) {
            for ($i = $row->no_awal; $i <= $row->no_akhir; $i++) {
                array_push($aktaFill, $i);
            }
        }
        sort($aktaFill);
        $temp = [];
        $result = "";
        for ($i = 1; $i <= $aktaFill[sizeof($aktaFill) - 1]; $i++) {
            if (!in_array($i, $aktaFill)) {
                array_push($temp, $i);
            } else {
                if (sizeof($temp) > 0) {
                    if (sizeof($temp) == 1) {
                        $result = $result . $i . ", ";
                    } else if (sizeof($temp) > 1) {
                        $result = $result . $temp[0] . "-" . $temp[sizeof($temp) - 1] . ", ";
                    }
                    $temp = [];
                }
            }
        }
        $result = $result . ">" . $aktaFill[sizeof($aktaFill) - 1];
        return ResponseFormatter::success(
            $result,
            'Get Akta Sisa Successfully'
        );
    }
}
