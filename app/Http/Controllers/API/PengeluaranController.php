<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\JenisPengeluaran;
use App\Models\KategoriPengeluaran;
use App\Models\Pengeluaran;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PengeluaranController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $request->validate([
                'kategori_pengeluaran_id' => 'required',
                'jenis_pengeluaran_id' => 'required',
                'tanggal' => 'required|date',
                'jumlah' => 'required|integer',
                'deskripsi' => 'required',
            ]);

            $pengeluaran = Pengeluaran::create([
                'user_id' => Auth::id(),
                'kategori_pengeluaran_id' => $request->kategori_pengeluaran_id,
                'jenis_pengeluaran_id' => $request->jenis_pengeluaran_id,
                'tanggal' => $request->tanggal,
                'jumlah' => $request->jumlah,
                'deskripsi' => $request->deskripsi,
            ]);

            return ResponseFormatter::success(
                $pengeluaran->load(['kategori', 'jenis']),
                'Create Pengeluaran Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Pengeluaran Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Pengeluaran Failed',
                500,
            );
        }
    }

    // CREATE Link Gaji
    public function createLinkGaji(Request $request)
    {
        try {
            $request->validate([
                'tanggal' => 'required|date',
                'jumlah' => 'required|integer',
            ]);

            $kategori_pengeluaran = KategoriPengeluaran::where('nama', 'Biaya')->first();
            if (!$kategori_pengeluaran) {
                $kategori_pengeluaran = KategoriPengeluaran::create([
                    'nama' => 'Biaya',
                ]);
            }

            $jenis_pengeluaran = JenisPengeluaran::where('nama', 'Biaya Gaji')->first();
            if (!$jenis_pengeluaran) {
                $jenis_pengeluaran = JenisPengeluaran::create([
                    'nama' => 'Biaya Gaji',
                ]);
            }

            $bulan = date('n', strtotime($request->tanggal));
            $tahun = date('Y', strtotime($request->tanggal));
            $pengeluaran = Pengeluaran::where('jenis_pengeluaran_id', $jenis_pengeluaran->id)->where('kategori_pengeluaran_id', $kategori_pengeluaran->id)->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)->first();
            if (!$pengeluaran) {
                $pengeluaran = Pengeluaran::create([
                    'user_id' => Auth::id(),
                    'kategori_pengeluaran_id' => $kategori_pengeluaran->id,
                    'jenis_pengeluaran_id' => $jenis_pengeluaran->id,
                    'tanggal' => $request->tanggal,
                    'jumlah' => $request->jumlah,
                    'deskripsi' => 'Gaji ' . $bulan . '-' . $tahun,
                ]);
            } else {
                $pengeluaran->update([
                    'user_id' => Auth::id(),
                    'tanggal' => $request->tanggal,
                    'jumlah' => $request->jumlah,
                ]);
            }

            return ResponseFormatter::success(
                $pengeluaran->load(['kategori', 'jenis']),
                'Create Pengeluaran Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Pengeluaran Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Pengeluaran Failed',
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

        $pengeluaran = Pengeluaran::with(['user', 'kategori', 'jenis']);

        if ($user_id) {
            $pengeluaran->where('user_id', $user_id);
        }

        if ($kategori_id) {
            $pengeluaran->where(function ($query) use ($kategori_id) {
                foreach ($kategori_id as $value) {
                    $query->orWhere('kategori_pengeluaran_id', $value);
                }
                return $query;
            });
        }

        if ($month && $year) {
            $pengeluaran->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
        }

        if ($search) {
            $pengeluaran->select("pengeluaran.*")
                ->join('kategori_pengeluaran', 'pengeluaran.kategori_pengeluaran_id', '=', 'kategori_pengeluaran.id')
                ->join('jenis_pengeluaran', 'pengeluaran.jenis_pengeluaran_id', '=', 'jenis_pengeluaran.id')
                ->where(function ($query) use ($search) {
                    return $query
                        ->orWhere('tanggal', 'like', '%' . $search . '%')
                        ->orWhere('kategori_pengeluaran.nama', 'like', '%' . $search . '%')
                        ->orWhere('jenis_pengeluaran.nama', 'like', '%' . $search . '%')
                        ->orWhere('jumlah', 'like', '%' . $search . '%')
                        ->orWhere('deskripsi', 'like', '%' . $search . '%');
                });
        }

        $total = $pengeluaran->sum('jumlah');

        return ResponseFormatter::success(
            [
                'total_pengeluaran' => $total,
                'table' => $pengeluaran->orderBy('tanggal', 'DESC')->paginate($limit)
            ],
            'Get Pengeluaran Successfully'
        );
    }

    // UPDATE
    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'kategori_pengeluaran_id' => 'required',
                'jenis_pengeluaran_id' => 'required',
                'tanggal' => 'required|date',
                'jumlah' => 'required|integer',
                'deskripsi' => 'required',
            ]);

            $pengeluaran = Pengeluaran::find($request->id);

            if (!$pengeluaran) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Data not found',
                    ],
                    'Edit Pengeluaran Failed',
                    404,
                );
            }

            $pengeluaran->update([
                'user_id' => Auth::id(),
                'kategori_pengeluaran_id' => $request->kategori_pengeluaran_id,
                'jenis_pengeluaran_id' => $request->jenis_pengeluaran_id,
                'tanggal' => $request->tanggal,
                'jumlah' => $request->jumlah,
                'deskripsi' => $request->deskripsi,
            ]);

            return ResponseFormatter::success(
                $pengeluaran,
                'Edit Pengeluaran Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Edit Pengeluaran Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Edit Pengeluaran Failed',
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
                $pengeluaran = Pengeluaran::find($id);

                if (!$pengeluaran) {
                    return ResponseFormatter::error(
                        [
                            'message' => 'Something when wrong',
                            'error' => 'Some Data Not Found',
                        ],
                        'Delete Pengeluaran Failed',
                        404,
                    );
                }

                $pengeluaran->forceDelete();
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
