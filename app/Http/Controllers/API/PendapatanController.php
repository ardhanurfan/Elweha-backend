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
                $pendapatan->load('kategori'),
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
        $kategori_id = $request->input('kategori_id', []);
        $month = $request->input('month');
        $year = $request->input('year');
        $search = $request->input('search');

        $pendapatan = Pendapatan::with(['user', 'kategori']);

        if ($user_id) {
            $pendapatan->where('user_id', $user_id);
        }

        if ($kategori_id) {
            $pendapatan->where(function ($query) use ($kategori_id) {
                foreach ($kategori_id as $value) {
                    $query->orWhere('kategori_pendapatan_id', $value);
                }
                return $query;
            });
        }

        if ($month && $year) {
            $pendapatan->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
        }

        if ($search) {
            $pendapatan->join('kategori_pendapatan', 'kategori_pendapatan.id', '=', 'pendapatan.kategori_pendapatan_id')
                ->where(function ($query) use ($search) {
                    return $query
                        ->orWhere('tanggal', 'like', '%' . $search . '%')
                        ->orWhere('kategori_pendapatan.nama', 'like', '%' . $search . '%')
                        ->orWhere('jumlah', 'like', '%' . $search . '%')
                        ->orWhere('pengirim', 'like', '%' . $search . '%')
                        ->orWhere('deskripsi', 'like', '%' . $search . '%');
                });
        }

        return ResponseFormatter::success(
            $pendapatan->orderBy('tanggal', 'DESC')->paginate($limit),
            'Get Pendapatan Successfully'
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
