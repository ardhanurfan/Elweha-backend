<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\KategoriPendapatan;
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

    //UPLOAD
    public function upload(Request $request)
    {
        try {
            $data_input = $request->json()->all();

            foreach ($data_input as $data) {
                $kategori = KategoriPendapatan::where('nama', 'ILIKE', '%' . $data['kategori'] . '%')->first();
                if(!$kategori){
                    $kategori = KategoriPendapatan::create([
                        'nama' => $data['kategori']
                    ]);
                }
                $pendapatan = Pendapatan::create([
                    'user_id' => Auth::id(),
                    'kategori_pendapatan_id' => $kategori->id,
                    'tanggal' => $data['tanggal'],
                    'jumlah' => $data['jumlah'],
                    'pengirim' => $data['pengirim'],
                    'deskripsi' => $data['deskripsi'],
                ]);

            }

            return ResponseFormatter::success(
                $pendapatan->get(),
                'Upload Pendapatan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Upload Pendapatan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Upload Pendapatan Failed',
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

        if ($month) {
            $pendapatan->whereMonth('tanggal', $month);
        }

        if ($year) {
            $pendapatan->whereYear('tanggal', $year);
        }

        if ($search) {
            $pendapatan->select("pendapatan.*")
                ->join('kategori_pendapatan', 'pendapatan.kategori_pendapatan_id', '=', 'kategori_pendapatan.id')
                ->where(function ($query) use ($search) {
                    return $query
                        ->orWhere('tanggal', 'ILIKE', '%' . $search . '%')
                        ->orWhere('kategori_pendapatan.nama', 'ILIKE', '%' . $search . '%')
                        ->orWhere('jumlah', 'ILIKE', '%' . $search . '%')
                        ->orWhere('pengirim', 'ILIKE', '%' . $search . '%')
                        ->orWhere('deskripsi', 'ILIKE', '%' . $search . '%');
                });
        }

        $total = $pendapatan->sum('jumlah');

        return ResponseFormatter::success(
            [
                'total_pendapatan' => $total,
                'table' => $pendapatan->orderBy('tanggal', 'DESC')->paginate($limit)
            ],
            'Get Pendapatan Successfully'
        );
    }

    // UPDATE
    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'kategori_pendapatan_id' => 'required',
                'tanggal' => 'required|date',
                'jumlah' => 'required|integer',
                'pengirim' => 'required',
                'deskripsi' => 'required',
            ]);

            $pendapatan = Pendapatan::find($request->id);

            if (!$pendapatan) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Data not found',
                    ],
                    'Edit Pendapatan Failed',
                    404,
                );
            }

            $pendapatan->update([
                'user_id' => Auth::id(),
                'kategori_pendapatan_id' => $request->kategori_pendapatan_id,
                'tanggal' => $request->tanggal,
                'jumlah' => $request->jumlah,
                'pengirim' => $request->pengirim,
                'deskripsi' => $request->deskripsi,
            ]);

            return ResponseFormatter::success(
                $pendapatan,
                'Edit Pendapatan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Edit Pendapatan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Edit Pendapatan Failed',
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
                $pendapatan = Pendapatan::find($id);

                if (!$pendapatan) {
                    return ResponseFormatter::error(
                        [
                            'message' => 'Something when wrong',
                            'error' => 'Some Data Not Found',
                        ],
                        'Delete Pendapatan Failed',
                        404,
                    );
                }

                $pendapatan->forceDelete();
            }

            return ResponseFormatter::success(
                null,
                'Delete Pendapatan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Pendapatan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Pendapatan Failed',
                500,
            );
        }
    }
}
