<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Gaji;
use App\Models\Kehadiran;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KehadiranController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $data_input = $request->json()->all();

            foreach ($data_input as $data) {
                // Search berdasar nama
                $karyawan = Gaji::where('nama_karyawan', 'like', '%' . $data['nama_karyawan'] . '%')->first();

                if ($karyawan) {
                    // hapus 00
                    $nol =
                        Kehadiran::where('gaji_id', $karyawan->id)->where('bulan', 0)->where('tahun', 0)->first();
                    if ($nol) {
                        $nol->forceDelete();
                    }

                    $bulan = date('n', strtotime($data['tanggal']));
                    $tahun = date('Y', strtotime($data['tanggal']));
                    $kehadiran = Kehadiran::where('gaji_id', $karyawan->id)->where('bulan', $bulan)->where('tahun', $tahun)->first();
                    if ($kehadiran) {
                        $kehadiran->update([
                            'kehadiran_actual' => $data['kehadiran_actual'],
                            'kehadiran_standart' => $data['kehadiran_standart'],
                            'keterlambatan' => $data['keterlambatan'],
                        ]);
                    } else {
                        Kehadiran::create([
                            'gaji_id' => $karyawan->id,
                            'besar_gaji' => $karyawan->besar_gaji,
                            'kehadiran_actual' => $data['kehadiran_actual'],
                            'kehadiran_standart' => $data['kehadiran_standart'],
                            'keterlambatan' => $data['keterlambatan'],
                            'bulan' => $bulan,
                            'tahun' => $tahun,
                        ]);
                    }
                }
            }

            $kehadiran = Kehadiran::with('gaji');

            return ResponseFormatter::success(
                $kehadiran->get(),
                'Upload Presensi Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Upload Presensi Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Upload Presensi Failed',
                500,
            );
        }
    }
}
