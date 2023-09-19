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

class RekanController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $request->validate([
                'nama_rekan' => 'required|unique:rekan,nama',
                'biaya_jasa' => 'required|integer',
            ]);

            $rekan = Rekan::create([
                'user_id' => Auth::id(),
                'nama' => $request->nama_rekan,
                'biaya_jasa' => $request->biaya_jasa,
            ]);

            PajakRekan::create([
                'user_id' => Auth::id(),
                'rekan_id' => $rekan->id,
                'biaya_jasa' => $rekan->biaya_jasa,
            ]);

            return ResponseFormatter::success(
                $rekan,
                'Create Rekan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Rekan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Rekan Failed',
                500,
            );
        }
    }

    // READ
    public function read(Request $request)
    {
        $rekan = Rekan::query();
        if ($request->id) {
            $rekan->where('id', $request->id)->first();
        }
        return ResponseFormatter::success($rekan->get(), 'Get Rekan Data Success');
    }

    // UPDATE
    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'nama_rekan' => 'required|unique:rekan,nama,' . $request->id,
                'biaya_jasa' => 'required|integer',
                'tahun' => 'required'
            ]);

            $rekan = Rekan::find($request->id);

            if (!$rekan) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Data not found',
                    ],
                    'Edit Rekan Failed',
                    404,
                );
            }

            $rekan->update([
                'user_id' => Auth::id(),
                'nama' => $request->nama_rekan,
            ]);

            $pajak_rekan = PajakRekan::where('tahun', $request->tahun)->where('rekan_id', $request->id);
            $pajak_rekan->update([
                'biaya_jasa' => $request->biaya_jasa,
            ]);

            if ((PajakRekan::orderBy('tahun', 'DESC')->first()->tahun == $request->tahun) || !$pajak_rekan->first()) {
                $rekan->update([
                    'biaya_jasa' => $request->biaya_jasa,
                ]);
            }

            return ResponseFormatter::success(
                $rekan,
                'Edit Rekan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Edit Rekan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Edit Rekan Failed',
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
                $rekan = Rekan::find($id);

                if (!$rekan) {
                    return ResponseFormatter::error(
                        [
                            'message' => 'Something when wrong',
                            'error' => 'Some Data not found',
                        ],
                        'Delete Rekan Failed',
                        404,
                    );
                }

                $rekan->forceDelete();
            }

            return ResponseFormatter::success(
                null,
                'Delete Rekan Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Rekan Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Rekan Failed',
                500,
            );
        }
    }
}
