<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\VariabelBonus;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VariabelBonusController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $request->validate(
                [
                    'nama_bonus' => 'required',
                    'besar_bonus' => 'required',
                    'kehadiran_id' => 'required',
                    'jumlah' => 'required',
                ],
                [
                    'kehadiran_id.required' => 'Presensi bulan ini belum diupload',
                ]
            );

            $variabel_bonus = VariabelBonus::create([
                'nama_bonus' => $request->nama_bonus,
                'besar_bonus' => $request->besar_bonus,
                'kehadiran_id' => $request->kehadiran_id,
                'jumlah' => $request->jumlah,
                'total' => $request->jumlah * $request->besar_bonus,
            ]);

            return ResponseFormatter::success(
                $variabel_bonus,
                'Create Variabel Bonus Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Variabel Bonus Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Variabel Bonus Failed',
                500,
            );
        }
    }

    // DELETE
    public function delete(Request $request)
    {
        try {
            $request->validate([
                'kehadiran_id' => 'required',
                'id' => 'required',
            ]);

            $bonus_variabel = VariabelBonus::where('kehadiran_id', $request->kehadiran_id)->where('id', $request->id)->first();

            if (!$bonus_variabel) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Some Data not found',
                    ],
                    'Delete Variabel Bonus Failed',
                    404,
                );
            }

            $bonus_variabel->forceDelete();

            return ResponseFormatter::success(
                null,
                'Delete Variabel Bonus Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Variabel Bonus Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Variabel Bonus Failed',
                500,
            );
        }
    }
}
