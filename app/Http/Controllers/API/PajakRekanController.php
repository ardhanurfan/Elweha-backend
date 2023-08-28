<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\PajakRekan;
use App\Models\Rekan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PajakRekanController extends Controller
{
    function hitungPajak($dpp_akumulasi)
    {
        $satu_juta = 1000000;
        if ($dpp_akumulasi <= 60 * $satu_juta) {
            return $dpp_akumulasi * 0.05;
        } else if ($dpp_akumulasi > 60 * $satu_juta && $dpp_akumulasi <= 250 * $satu_juta) {
            return (60 * $satu_juta * 0.05) + (($dpp_akumulasi - 60 * $satu_juta) * 0.15);
        } else if ($dpp_akumulasi > 250 * $satu_juta && $dpp_akumulasi <= 500 * $satu_juta) {
            return (60 * $satu_juta * 0.05) + (190 * $satu_juta * 0.15) + (($dpp_akumulasi - 250 * $satu_juta) * 0.25);
        } else if ($dpp_akumulasi > 500 * $satu_juta && $dpp_akumulasi <= 5000 * $satu_juta) {
            return (60 * $satu_juta * 0.05) + (190 * $satu_juta * 0.15) + (250 * $satu_juta * 0.25) + (($dpp_akumulasi - 500 * $satu_juta) * 0.3);
        } else {
            return (60 * $satu_juta * 0.05) + (190 * $satu_juta * 0.15) + (250 * $satu_juta * 0.25) + (4500 * $satu_juta * 0.3) + (($dpp_akumulasi - 5000 * $satu_juta) * 0.35);
        }
    }


    // CREATE
    public function create(Request $request)
    {
    }

    // READ
    public function readByRekanId(Request $request)
    {
        $user_id = $request->input('user_id');
        $limit = $request->input('limit');
        $rekan_id = $request->input('rekan_id');
        $year = $request->input('year');

        $pajak_rekan = PajakRekan::with(["rekan"])->where('rekan_id', $rekan_id);

        if ($user_id) {
            $pajak_rekan->where('pajak_rekan.user_id', $user_id);
        }

        if ($year) {
            $pajak_rekan->where('tahun', $year);
        }

        $pajak_rekan = $pajak_rekan->orderBy('tahun')->orderBy('bulan')->paginate($limit)->toArray();

        $pajak_rekan_data = $pajak_rekan['data'];
        $data = [];

        foreach ($pajak_rekan_data as $data_bulan) {
            $cek_include = array_values(array_filter($data, function ($var) use ($data_bulan) {
                return $data_bulan['rekan']['nama'] == $var['nama'] && $data_bulan['tahun'] == $var['tahun'];
            }));


            if ($cek_include) {
                $last = $cek_include[sizeof($cek_include) - 1];

                $jasa_bruto = $data_bulan['biaya_jasa'] * $data_bulan['jumlah_akta'];
                $dpp = $jasa_bruto * 0.5;
                $dpp_akumulasi = $dpp + $last['dpp_akumulasi'];
                $pph = PajakRekanController::hitungPajak($dpp_akumulasi);
                $pph_akumulasi = $pph + $last['pph_akumulasi'];
                $transfer = $jasa_bruto - $pph;
                array_push($data, [
                    'id' => $data_bulan['id'],
                    'rekan_id' => $data_bulan['rekan']['id'],
                    'nama' => $data_bulan['rekan']['nama'],
                    'jumlah_akta' => $data_bulan['jumlah_akta'],
                    'biaya_jasa' => $data_bulan['biaya_jasa'],
                    'jasa_bruto' => $jasa_bruto,
                    'dpp' => $dpp,
                    'dpp_akumulasi' => $dpp_akumulasi,
                    'pph' => $pph,
                    'pph_akumulasi' => $pph_akumulasi,
                    'transfer' => $transfer,
                    'bulan' => $data_bulan['bulan'],
                    'tahun' => $data_bulan['tahun'],
                ]);
            } else {
                $jasa_bruto = $data_bulan['biaya_jasa'] * $data_bulan['jumlah_akta'];
                $dpp = $jasa_bruto * 0.5;
                $dpp_akumulasi = $dpp;
                $pph = PajakRekanController::hitungPajak($dpp_akumulasi);
                $pph_akumulasi = $pph;
                $transfer = $jasa_bruto - $pph;
                array_push($data, [
                    'id' => $data_bulan['id'],
                    'rekan_id' => $data_bulan['rekan']['id'],
                    'nama' => $data_bulan['rekan']['nama'],
                    'jumlah_akta' => $data_bulan['jumlah_akta'],
                    'biaya_jasa' => $data_bulan['biaya_jasa'],
                    'jasa_bruto' => $jasa_bruto,
                    'dpp' => $dpp,
                    'dpp_akumulasi' => $dpp_akumulasi,
                    'pph' => $pph,
                    'pph_akumulasi' => $pph_akumulasi,
                    'transfer' => $transfer,
                    'bulan' => $data_bulan['bulan'],
                    'tahun' => $data_bulan['tahun'],
                ]);
            }
        }

        return ResponseFormatter::success(
            [
                'table' => [
                    'last_page' => $pajak_rekan['last_page'],
                    'total' => $pajak_rekan['total'],
                    'data' => $data,
                ]
            ],
            'Get Pajak Rekan Successfully'
        );
    }

    // READ
    public function readByTahun(Request $request)
    {
        $user_id = $request->input('user_id');
        $limit = $request->input('limit');
        $search = $request->input('search');
        $year = $request->input('year');

        $pajak_rekan = PajakRekan::with(["rekan"]);
        $rekan = Rekan::query();

        if ($user_id) {
            $pajak_rekan->where('pajak_rekan.user_id', $user_id);
            $rekan->where('rekan.user_id', $user_id);
        }


        if ($search) {
            $rekan->where(
                function ($query) use ($search) {
                    return $query->orWhere('nama', 'like', '%' . $search . '%');
                }
            );
        }

        if ($year) {
            $pajak_rekan->where('tahun', $year)->orWhere('tahun', 0);
        }

        $rekan = $rekan->orderBy('created_at', 'Desc')->paginate($limit)->toArray();
        $pajak_rekan = $pajak_rekan->orderBy('tahun')->orderBy('bulan')->get()->toArray();

        $data = [];
        foreach ($pajak_rekan as $data_bulan) {
            $cek_include = array_values(array_filter($data, function ($var) use ($data_bulan) {
                return $data_bulan['rekan']['nama'] == $var['nama'] && $data_bulan['tahun'] == $var['tahun'];
            }));


            if ($cek_include) {
                $last = $cek_include[sizeof($cek_include) - 1];

                $jasa_bruto = $data_bulan['biaya_jasa'] * $data_bulan['jumlah_akta'];
                $dpp = $jasa_bruto * 0.5;
                $dpp_akumulasi = $dpp + $last['dpp_akumulasi'];
                $pph = PajakRekanController::hitungPajak($dpp_akumulasi);
                $pph_akumulasi = $pph + $last['pph_akumulasi'];
                $transfer = $jasa_bruto - $pph;
                array_push($data, [
                    'id' => $data_bulan['id'],
                    'rekan_id' => $data_bulan['rekan']['id'],
                    'nama' => $data_bulan['rekan']['nama'],
                    'jumlah_akta' => $data_bulan['jumlah_akta'],
                    'biaya_jasa' => $data_bulan['biaya_jasa'],
                    'jasa_bruto' => $jasa_bruto,
                    'dpp' => $dpp,
                    'dpp_akumulasi' => $dpp_akumulasi,
                    'pph' => $pph,
                    'pph_akumulasi' => $pph_akumulasi,
                    'transfer' => $transfer,
                    'bulan' => $data_bulan['bulan'],
                    'tahun' => $data_bulan['tahun'],
                ]);
            } else {
                $jasa_bruto = $data_bulan['biaya_jasa'] * $data_bulan['jumlah_akta'];
                $dpp = $jasa_bruto * 0.5;
                $dpp_akumulasi = $dpp;
                $pph = PajakRekanController::hitungPajak($dpp_akumulasi);
                $pph_akumulasi = $pph;
                $transfer = $jasa_bruto - $pph;
                array_push($data, [
                    'id' => $data_bulan['id'],
                    'rekan_id' => $data_bulan['rekan']['id'],
                    'nama' => $data_bulan['rekan']['nama'],
                    'jumlah_akta' => $data_bulan['jumlah_akta'],
                    'biaya_jasa' => $data_bulan['biaya_jasa'],
                    'jasa_bruto' => $jasa_bruto,
                    'dpp' => $dpp,
                    'dpp_akumulasi' => $dpp_akumulasi,
                    'pph' => $pph,
                    'pph_akumulasi' => $pph_akumulasi,
                    'transfer' => $transfer,
                    'bulan' => $data_bulan['bulan'],
                    'tahun' => $data_bulan['tahun'],
                ]);
            }
        }


        $data_per_tahun = [];
        $total_transfer = 0;
        foreach ($rekan['data'] as $rkn) {
            $jumlah_akta = 0;
            $jasa_bruto = 0;
            $dpp = 0;
            $dpp_akumulasi = 0;
            $pph = 0;
            $pph_akumulasi = 0;
            $transfer = 0;
            $biaya_jasa = $rkn['biaya_jasa'];
            foreach ($data as $dt) {
                if ($rkn['nama'] == $dt['nama']) {
                    $jumlah_akta += $dt['jumlah_akta'];
                    $jasa_bruto += $dt['jasa_bruto'];
                    $dpp += $dt['dpp'];
                    $dpp_akumulasi += $dt['dpp_akumulasi'];
                    $pph += $dt['pph'];
                    $pph_akumulasi += $dt['pph_akumulasi'];
                    $transfer += $dt['transfer'];
                    $biaya_jasa = $dt['biaya_jasa'];
                }
            }
            array_push($data_per_tahun, [
                'rekan_id' => $rkn['id'],
                'nama' => $rkn['nama'],
                'jumlah_akta' => $jumlah_akta,
                'biaya_jasa' => $biaya_jasa,
                'jasa_bruto' => $jasa_bruto,
                'dpp' => $dpp,
                'dpp_akumulasi' => $dpp_akumulasi,
                'pph' => $pph,
                'pph_akumulasi' => $pph_akumulasi,
                'transfer' => $transfer,
            ]);
            $total_transfer += $transfer;
        }

        return ResponseFormatter::success(
            [
                'total_transfer' => $total_transfer,
                'table' => [
                    'last_page' => $rekan['last_page'],
                    'total' => $rekan['total'],
                    'data' => $data_per_tahun,
                ]
            ],
            'Get Pajak Rekan Successfully'
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
