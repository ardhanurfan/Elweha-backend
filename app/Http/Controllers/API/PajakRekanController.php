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
        try {
            $request->validate([
                'nama_rekan' => 'required|unique:pajak_rekan,nama',
                'biaya_jasa' => 'required|integer',
            ]);

            $pajak_rekan = PajakRekan::create([
                'user_id' => Auth::id(),
                'nama' => $request->nama_rekan,
                'biaya_jasa' => $request->biaya_jasa,
            ]);

            return ResponseFormatter::success(
                $pajak_rekan,
                'Create Pajak Rekan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Pajak Rekan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Pajak Rekan Failed',
                500,
            );
        }
    }

    // READ
    public function read(Request $request)
    {
        $user_id = $request->input('user_id');
        $limit = $request->input('limit');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $search = $request->input('search');

        $pajak_rekan = PajakRekan::select("pajak_rekan.*")
            ->leftJoin('pajak_rekan_akta', 'pajak_rekan_akta.pajak_rekan_id', '=', 'pajak_rekan.id')
            ->where(function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->where('tanggal', '>=', $startDate)->where('tanggal', '<=', $endDate);
                }
                return $query
                    ->orderBy('tanggal', 'DESC');
            });

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
        try {
            $nama = $request->input('nama_rekan');

            $request->validate([
                'id' => 'required',
                'nama_rekan' => 'required|unique:pajak_rekan,nama,' . $request->id,
                'biaya_jasa' => 'required|integer',
            ]);

            $pajak_rekan = PajakRekan::find($request->id);

            if (!$pajak_rekan) {
                return ResponseFormatter::error(
                    null,
                    'Data not found',
                    404
                );
            }

            $pajak_rekan->update([
                'user_id' => Auth::id(),
                'nama' => $request->nama_rekan,
                'biaya_jasa' => $request->biaya_jasa,
            ]);

            return ResponseFormatter::success(
                $pajak_rekan,
                'Edit Pajak Rekan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Edit Pajak Rekan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Edit Pajak Rekan Failed',
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
                $pajak_rekan = PajakRekan::find($id);

                if (!$pajak_rekan) {
                    return ResponseFormatter::error(
                        null,
                        'Some Data Not Found',
                        404
                    );
                }

                $pajak_rekan->forceDelete();
            }

            return ResponseFormatter::success(
                null,
                'Delete Pengeluaran Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Pengeluaran Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Pengeluaran Failed',
                500,
            );
        }
    }
}
