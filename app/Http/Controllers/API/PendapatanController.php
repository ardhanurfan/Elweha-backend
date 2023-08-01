<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Pendapatan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PendapatanController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required',
                'kategori_pendapatan_id' => 'required',
                'tanggal' => 'required|date',
                'jumlah' => 'required|integer',
                'pengirim' => 'required',
                'deskripsi' => 'required',
            ]);

            $pendapatan = Pendapatan::create([
                'user_id' => Auth::id(),
                'kategori_pendapatan_id' => $request->kategori_pendapatan_id,
                'tanggal' => $request->tanggal,
                'jumlah' => $request->jumlah,
                'pengirim' => $request->pengirim,
                'deskripsi' => $request->deskripsi,
            ]);

            return ResponseFormatter::success(
                $pendapatan,
                'Create Pendapatan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Pendapatan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Pendapatan Failed',
                500,
            );
        }
    }

    // READ
    public function read(Request $request)
    {
        $user_id = $request->input('user_id');
        $limit = $request->input('limit');
        $jumlah = $request->input('jumlah');
        $pengirim = $request->input('pengirim');
        $deskripsi = $request->input('deskripsi');
        $kategori_id = $request->input('kategori_id');
        $tanggal = $request->input('tanggal');
        $month = $request->input('month');
        $year = $request->input('year');

        $pendapatan = Pendapatan::with(['user', 'kategori']);

        if ($user_id) {
            $pendapatan->where('user_id', $user_id);
        }

        if ($jumlah) {
            $pendapatan->where('jumal$jumlah', 'like', '%' . $jumlah . '%');
        }

        if ($pengirim) {
            $pendapatan->where('pengirim', 'like', '%' . $pengirim . '%');
        }

        if ($deskripsi) {
            $pendapatan->where('deskripsi', 'like', '%' . $deskripsi . '%');
        }

        if ($kategori_id) {
            $pendapatan->where('kategori_id', $kategori_id);
        }

        if ($tanggal) {
            $pendapatan->where('tanggal', $tanggal);
        }

        if ($month && $year) {
            $pendapatan->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
        }

        return ResponseFormatter::success(
            $pendapatan->paginate($limit),
            'Data produk berhasil diambil'
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
