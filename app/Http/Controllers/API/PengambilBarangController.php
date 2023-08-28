<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\PengambilBarang;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PengambilBarangController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $request->validate([
                'barang_id' => 'required',
                'tanggal' => 'required|date',
                'nama_pengambil' => 'required|string',
                'jumlah' => 'required|integer|min:0',
            ]);

            $barang = Barang::find($request->barang_id);
            if (!$barang) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Some Data Not Found',
                    ],
                    'Create Pengambilan Failed',
                    404,
                );
            }
            if ($barang->jumlah < $request->jumlah) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Stok Tidak Cukup',
                    ],
                    'Create Pengambilan Failed',
                    404,
                );
            }

            $barang->update([
                'jumlah' => $barang->jumlah - $request->jumlah
            ]);

            $ambil = PengambilBarang::create([
                'user_id' => Auth::id(),
                'nama_pengambil' => $request->nama_pengambil,
                'barang_id' => $request->barang_id,
                'tanggal' => $request->tanggal,
                'jumlah' => $request->jumlah,
            ]);

            return ResponseFormatter::success(
                $ambil->load('barang'),
                'Create Pengambilan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Pengambilan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Pengambilan Failed',
                500,
            );
        }
    }

    // READ
    public function read(Request $request)
    {
        $user_id = $request->input('user_id');
        $limit = $request->input('limit');
        $jenis_id = $request->input('jenis_id', []);
        $month = $request->input('month');
        $year = $request->input('year');
        $search = $request->input('search');

        $ambil = PengambilBarang::with(['user', 'barang', 'barang.jenis']);

        if ($user_id) {
            $ambil->where('pengambil_barang.user_id', $user_id);
        }

        if ($jenis_id) {
            $ambil->select("pengambil_barang.*")
                ->join('barang', 'pengambil_barang.barang_id', '=', 'barang.id')
                ->where(function ($query) use ($jenis_id) {
                    foreach ($jenis_id as $value) {
                        $query->orWhere('barang.jenis_barang_id', $value);
                    }
                    return $query;
                });
        }

        if ($month && $year) {
            $ambil->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
        }

        if ($search) {
            if ($jenis_id) {
                $ambil->select("pengambil_barang.*")
                    ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id');
            } else {
                $ambil->select("pengambil_barang.*")
                    ->join('barang', 'pengambil_barang.barang_id', '=', 'barang.id')
                    ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id');
            }

            $ambil->where(function ($query) use ($search) {
                return $query
                    ->orWhere('tanggal', 'like', '%' . $search . '%')
                    ->orWhere('nama_pengambil', 'like', '%' . $search . '%')
                    ->orWhere('nama_barang', 'like', '%' . $search . '%')
                    ->orWhere('jenis_barang.nama', 'like', '%' . $search . '%')
                    ->orWhere('pengambil_barang.jumlah', 'like', '%' . $search . '%')
                    ->orWhere('barang.satuan', 'like', '%' . $search . '%');
            });
        }


        return ResponseFormatter::success(
            $ambil->orderBy('tanggal', 'DESC')->paginate($limit),
            'Get Pengambilan Successfully'
        );
    }

    // UPDATE
    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'barang_id' => 'required',
                'tanggal' => 'required|date',
                'nama_pengambil' => 'required|string',
                'jumlah' => 'required|integer|min:0',
            ]);

            $ambil = PengambilBarang::find($request->id);
            if (!$ambil) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Data not found',
                    ],
                    'Edit Pengambilan Failed',
                    404,
                );
            }

            $barang = Barang::find($request->barang_id);
            if (!$barang) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Some Data Not Found',
                    ],
                    'Edit Pengambilan Failed',
                    404,
                );
            }
            if (($barang->jumlah + $ambil->jumlah) < $request->jumlah) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Stok Tidak Cukup',
                    ],
                    'Edit Pengambilan Failed',
                    404,
                );
            }

            $barang->update([
                'jumlah' => $barang->jumlah + $ambil->jumlah - $request->jumlah
            ]);

            $ambil->update([
                'user_id' => Auth::id(),
                'nama_pengambil' => $request->nama_pengambil,
                'barang_id' => $request->barang_id,
                'tanggal' => $request->tanggal,
                'jumlah' => $request->jumlah,
            ]);

            return ResponseFormatter::success(
                $ambil,
                'Edit Pengambilan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Edit Pengambilan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Edit Pengambilan Failed',
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
                $ambil = PengambilBarang::find($id);

                $barang = Barang::find($ambil->barang_id);
                if (!$barang) {
                    return ResponseFormatter::error(
                        [
                            'message' => 'Something when wrong',
                            'error' => 'Some Data Not Found',
                        ],
                        'Edit Pengambilan Failed',
                        404,
                    );
                }

                $barang->update([
                    'jumlah' => $barang->jumlah + $ambil->jumlah
                ]);

                if (!$ambil) {
                    return ResponseFormatter::error(
                        [
                            'message' => 'Something when wrong',
                            'error' => 'Some Data Not Found',
                        ],
                        'Delete Pengambilan Failed',
                        404,
                    );
                }

                $ambil->forceDelete();
            }

            return ResponseFormatter::success(
                null,
                'Delete Pengambilan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Pengambilan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Pengambilan Failed',
                500,
            );
        }
    }
}
