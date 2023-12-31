<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BarangController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $request->validate([
                'jenis_barang_id' => 'required',
                'nama_barang' => 'required|string|unique:barang,nama_barang',
                'kategori_barang' => 'required|string',
                'jumlah' => 'required|integer|min:0',
                'satuan' => 'required|string',
                'tanggal' => 'required|date',
            ]);

            $barang = Barang::create([
                'user_id' => Auth::id(),
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $request->kategori_barang,
                'jenis_barang_id' => $request->jenis_barang_id,
                'jumlah' => $request->jumlah,
                'satuan' => $request->satuan,
                'tanggal' => $request->tanggal,
            ]);

            return ResponseFormatter::success(
                $barang->load('jenis'),
                'Create Barang Data Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Barang Data Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Barang Data Failed',
                500,
            );
        }
    }

    // READ
    public function read(Request $request)
    {
        $user_id = $request->input('user_id');
        $kategori_barang = $request->input('kategori_barang');
        $limit = $request->input('limit');
        $jenis_id = $request->input('jenis_id', []);
        $month = $request->input('month');
        $year = $request->input('year');
        $search = $request->input('search');

        $barang = Barang::with(['user', 'jenis']);

        if ($user_id) {
            $barang->where('user_id', $user_id);
        }

        if ($kategori_barang) {
            $barang->where('kategori_barang', $kategori_barang);
        }

        if ($jenis_id) {
            $barang->where(function ($query) use ($jenis_id) {
                foreach ($jenis_id as $value) {
                    $query->orWhere('jenis_barang_id', $value);
                }
                return $query;
            });
        }

        if ($month && $year) {
            $barang->select("barang.*")
                ->join('pengambil_barang', 'barang.id', '=', 'pengambil_barang.barang_id')
                ->whereMonth('pengambil_barang.tanggal', $month)->whereYear('pengambil_barang.tanggal', $year);
        }

        if ($search) {
            $barang->select("barang.*")
                ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
                ->where(function ($query) use ($search) {
                    return $query
                        ->orWhere('jenis_barang.nama', 'ILIKE', '%' . $search . '%')
                        ->orWhere('nama_barang', 'ILIKE', '%' . $search . '%')
                        ->orWhere('barang.jumlah', 'ILIKE', '%' . $search . '%')
                        ->orWhere('satuan', 'ILIKE', '%' . $search . '%');
                });
        }


        return ResponseFormatter::success(
            $barang->orderBy('nama_barang')->paginate($limit),
            'Get Barang Data Successfully'
        );
    }

    // UPDATE
    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'jenis_barang_id' => 'required',
                'nama_barang' => 'required|string|unique:barang,nama_barang,' . $request->id,
                'jumlah' => 'required|integer|min:0',
                'satuan' => 'required|string',
                'tanggal' => 'required|date',
            ]);

            $barang = Barang::find($request->id);

            if (!$barang) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Data not found',
                    ],
                    'Edit Barang Data Failed',
                    404,
                );
            }

            $barang->update([
                'user_id' => Auth::id(),
                'nama_barang' => $request->nama_barang,
                'jenis_barang_id' => $request->jenis_barang_id,
                'jumlah' => $request->jumlah,
                'satuan' => $request->satuan,
                'tanggal' => $request->tanggal,
            ]);

            return ResponseFormatter::success(
                $barang,
                'Edit Barang Data Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Edit Barang Data Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Edit Barang Data Failed',
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
                $barang = Barang::find($id);

                if (!$barang) {
                    return ResponseFormatter::error(
                        [
                            'message' => 'Something when wrong',
                            'error' => 'Some Data Not Found',
                        ],
                        'Delete Barang Data Failed',
                        404,
                    );
                }

                $barang->forceDelete();
            }

            return ResponseFormatter::success(
                null,
                'Delete Barang Data Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Barang Data Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Barang Data Failed',
                500,
            );
        }
    }
}
