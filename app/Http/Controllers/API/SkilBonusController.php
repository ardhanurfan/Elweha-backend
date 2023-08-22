<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\SkilBonus;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SkilBonusController extends Controller
{
    // CREATE
    public function create(Request $request)
    {
        try {
            $request->validate([
                'nama_bonus' => 'required',
                'besar_bonus' => 'required',
                'gaji_id' => 'required',
            ]);

            $skil_bonus = SkilBonus::create([
                'nama_bonus' => $request->nama_bonus,
                'besar_bonus' => $request->besar_bonus,
                'gaji_id' => $request->gaji_id,
            ]);

            return ResponseFormatter::success(
                $skil_bonus,
                'Create Skil Bonus Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Create Skil Bonus Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Create Skil Bonus Failed',
                500,
            );
        }
    }

    // DELETE
    public function delete(Request $request)
    {
        try {
            $request->validate([
                'gaji_id' => 'required',
                'id' => 'required',
            ]);

            $skil_bonus = SkilBonus::where('gaji_id', $request->gaji_id)->where('id', $request->id)->first();

            if (!$skil_bonus) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Some Data not found',
                    ],
                    'Delete Skil Bonus Failed',
                    404,
                );
            }

            $skil_bonus->forceDelete();

            return ResponseFormatter::success(
                null,
                'Delete Skil Bonus Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete Skil Bonus Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete Skil Bonus Failed',
                500,
            );
        }
    }
}
