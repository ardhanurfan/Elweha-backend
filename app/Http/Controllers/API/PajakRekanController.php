<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\PajakRekan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PajakRekanController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
    }

    // READ
    public function read(Request $request)
    {
        $user_id = $request->input('user_id');
        $limit = $request->input('limit');
        // $startDate = $request->input('startDate');
        // $endDate = $request->input('endDate');
        $search = $request->input('search');

        // $pajak_rekan = PajakRekan::select("pajak_rekan.*")->distinct()
        //     ->leftJoin('pajak_rekan_akta', 'pajak_rekan_akta.pajak_rekan_id', '=', 'pajak_rekan.id')
        //     ->where(function ($query) use ($startDate, $endDate) {
        //         if ($startDate && $endDate) {
        //             $query->where('tanggal', '>=', $startDate)->where('tanggal', '<=', $endDate);
        //         }
        //         return $query
        //             ->orderBy('tanggal', 'DESC');
        //     });

        $pajak_rekan = PajakRekan::with(["rekan"]);

        if ($user_id) {
            $pajak_rekan->where('user_id', $user_id);
        }

        if ($search) {
            $pajak_rekan->where(function ($query) use ($search) {
                return $query
                    ->orWhere('nama', 'like', '%' . $search . '%')
                    ->orWhere('biaya_jasa', 'like', '%' . $search . '%')
                    ->orWhere('pajak_rekan.jumlah_akta', 'like', '%' . $search . '%')
                    ->orWhere('jasa_bruto', 'like', '%' . $search . '%')
                    ->orWhere('dpp', 'like', '%' . $search . '%')
                    ->orWhere('dpp_akumulasi', 'like', '%' . $search . '%')
                    ->orWhere('pph_dipotong', 'like', '%' . $search . '%')
                    ->orWhere('pajak_akumulasi', 'like', '%' . $search . '%')
                    ->orWhere('transfer', 'like', '%' . $search . '%');
            });
        }

        $total = $pajak_rekan->sum('transfer');

        return ResponseFormatter::success(
            [
                'total_transfer' => $total,
                'table' => $pajak_rekan->paginate($limit)
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
