<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Gaji;
use App\Models\Kehadiran;
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
                'nama_karyawan' => 'required|unique:gaji,nama_karyawan',
                'jenis_gaji' => 'required',
                'besar_gaji' => 'required|integer',
            ]);

            $gaji = Gaji::create([
                'user_id' => Auth::id(),
                'nama_karyawan' => $request->nama_karyawan,
                'jenis_gaji' => $request->jenis_gaji,
                'besar_gaji' => $request->besar_gaji,

            ]);

            Kehadiran::create([
                'gaji_id' => $gaji->id,
                'besar_gaji' => $gaji->besar_gaji,
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
        $id = $request->input('id');
        $limit = $request->input('limit');
        $search = $request->input('search');
        $jenis = $request->input('jenis', []);
        $month = $request->input('month');
        $year = $request->input('year');

        $gaji = Kehadiran::with(['variabel', 'skil'])->join('gaji', 'kehadiran.gaji_id', '=', 'gaji.id')->select('gaji.id as gaji_id', 'gaji.user_id', 'gaji.nama_karyawan', 'gaji.jenis_gaji', 'gaji.besar_gaji', 'kehadiran.*');
        $gaji_all = Gaji::query();

        if ($id) {
            $gaji->where('gaji_id', $id);
        }

        if ($user_id) {
            $gaji->where('user_id', $user_id);
        }

        if ($jenis) {
            $gaji->where(function ($query) use ($jenis) {
                foreach ($jenis as $value) {
                    $query->orWhere('jenis_gaji', 'like', $value);
                }
                return $query;
            });
            $gaji_all->where(function ($query) use ($jenis) {
                foreach ($jenis as $value) {
                    $query->orWhere('jenis_gaji', 'like', $value);
                }
                return $query;
            });
        }

        if ($search) {
            $gaji->where('nama_karyawan', 'like', '%' . $search . '%');
            $gaji_all->where('nama_karyawan', 'like', '%' . $search . '%');
        }

        if ($month && $year) {
            $gaji->where('bulan', $month)->where('tahun', $year);
        }

        $gaji_all = $gaji_all->orderBy('created_at', 'Desc')->paginate($id ? sizeof($gaji_all->get()) : $limit)->toArray(); // jika ambil by id data diambil semua
        $gaji = $gaji->get()->toArray();

        $data_per_bulan = [];
        $data_kosong = [];
        foreach ($gaji_all['data'] as $gjAll) {
            $isFound = false;
            foreach ($gaji as $gjGet) {
                if ($gjAll['id'] == $gjGet['gaji_id']) {
                    array_push($data_per_bulan, [
                        'gaji_id' => $gjAll['id'],
                        'nama_karyawan' => $gjAll['nama_karyawan'],
                        'jenis_gaji' => $gjAll['jenis_gaji'],
                        'besar_gaji' => $gjAll['besar_gaji'],
                        'bulan' => $gjGet['bulan'],
                        'tahun' => $gjGet['tahun'],
                        'kehadiran_actual' => $gjGet['kehadiran_actual'],
                        'kehadiran_standart' => $gjGet['kehadiran_standart'],
                        'keterlambatan' => $gjGet['keterlambatan'],
                        'variabel' => $gjGet['variabel'],
                        'skil' => $gjGet['skil'],
                    ]);
                    $isFound = true;
                }
            }

            if (!$isFound) {
                if ($id) { // handle jika get by id
                    if ($id == $gjAll['id']) {
                        array_push($data_kosong, [
                            'gaji_id' => $gjAll['id'],
                            'nama_karyawan' => $gjAll['nama_karyawan'],
                            'jenis_gaji' => $gjAll['jenis_gaji'],
                            'besar_gaji' => $gjAll['besar_gaji'],
                            'bulan' => 0,
                            'tahun' => 0,
                            'kehadiran_actual' => 0,
                            'kehadiran_standart' => 0,
                            'keterlambatan' => 0,
                            'variabel' => [],
                            'skil' => [],
                        ]);
                    }
                } else {
                    array_push($data_kosong, [
                        'gaji_id' => $gjAll['id'],
                        'nama_karyawan' => $gjAll['nama_karyawan'],
                        'jenis_gaji' => $gjAll['jenis_gaji'],
                        'besar_gaji' => $gjAll['besar_gaji'],
                        'bulan' => 0,
                        'tahun' => 0,
                        'kehadiran_actual' => 0,
                        'kehadiran_standart' => 0,
                        'keterlambatan' => 0,
                        'variabel' => [],
                        'skil' => [],
                    ]);
                }
            }
        }

        $data_per_bulan = array_merge($data_per_bulan, $data_kosong);

        return ResponseFormatter::success(
            [
                'last_page' => $gaji_all['last_page'],
                'total' => $gaji_all['total'],
                'data' => $data_per_bulan,
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
                'nama_karyawan' => 'required|unique:gaji,nama_karyawan,' . $request->id,
                'jenis_gaji' => 'required',
                'besar_gaji' => 'required|integer',
                'tahun' => 'required',
                'bulan' => 'required',

            ]);

            $gaji = Gaji::find($request->id);

            if (!$gaji) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Data not found',
                    ],
                    'Edit Gaji Failed',
                    404,
                );
            }

            $gaji->update([
                'user_id' => Auth::id(),
                'nama_karyawan' => $request->nama_karyawan,
                'jenis_gaji' => $request->jenis_gaji,
            ]);

            $kehadiran = Kehadiran::where('tahun', $request->tahun)->where('bulan', $request->bulan)->where('gaji_id', $request->id);
            $kehadiran->update([
                'besar_gaji' => $request->besar_gaji,
            ]);

            $terbaru = Kehadiran::orderBy('tahun', 'DESC')->orderBy('bulan', 'DESC')->first();
            if ($terbaru->tahun == $request->tahun && $terbaru->bulan == $request->bulan) {
                $gaji->update([
                    'besar_gaji' => $request->besar_gaji,
                ]);
            }

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
