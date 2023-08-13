<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // Login
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);

            // Cek apakah ada username dan password yang sesuai
            $credentials = request(['username', 'password']);

            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Unauthorized',
                        'error' => 'Password Incorrect'
                    ],
                    'Authentication Failed',
                    401
                );
            }

            $user = User::where('username', $request->username)->first();

            // cek ulang apakah password sesuai (opsional)
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'acess_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Login Successfully');
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0],
                ],
                'Login Failed',
                401,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Login Failed',
                500,
            );
        }
    }

    // add account
    public function add(Request $request)
    {
        try {
            $request->validate([
                'nama' => ['required', 'string'],
                'email' => ['required', 'email', 'string', 'max:255', 'unique:users', 'email:dns'],
                'username' => ['required', 'string', 'max:25', 'unique:users', 'min:6'],
                'password' => ['required', 'string', Password::defaults()],
                'role' => ['required', 'string', 'in:BOD,OFFICER'],
            ]);

            User::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            $user = User::where('email', $request->email)->first();

            return ResponseFormatter::success(
                [
                    'user' => $user
                ],
                'User Registered'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Register Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Register Failed',
                500,
            );
        }
    }

    // edit account
    public function edit(Request $request)
    {
        try {
            $request->validate([
                'id' => ['required'],
                'nama' => ['required', 'string'],
                'email' => ['required', 'email', 'string', 'max:255', 'unique:users,email,' . $request->id, 'email:dns'],
                'username' => ['required', 'string', 'max:25', 'unique:users,username,' . $request->id, 'min:6'],
                'password' => ['string', Password::defaults()],
                'role' => ['required', 'string', 'in:BOD,OFFICER'],
            ]);

            $user = User::find($request->id);
            if (!$user) {
                return ResponseFormatter::error(
                    [
                        'message' => 'Something when wrong',
                        'error' => 'Data not found',
                    ],
                    'Edit User Failed',
                    404,
                );
            }

            $user->update([
                'nama' => $request->nama,
                'email' => $request->email,
                'username' => $request->username,
                'role' => $request->role,
            ]);

            if ($request->password) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            $user = User::where('email', $request->email)->first();

            return ResponseFormatter::success(
                [
                    'user' => $user
                ],
                'Edit User Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Edit User Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Edit User Failed',
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
                $user = User::find($id);

                if (!$user) {
                    return ResponseFormatter::error(
                        [
                            'message' => 'Something when wrong',
                            'error' => 'Some Data Not Found',
                        ],
                        'Delete User Failed',
                        404,
                    );
                }

                $user->forceDelete();
            }

            return ResponseFormatter::success(
                null,
                'Delete User Successfully'
            );
        } catch (ValidationException $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => array_values($error->errors())[0][0],
                ],
                'Delete User Failed',
                400,
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Delete User Failed',
                500,
            );
        }
    }

    // Get data user nanti dari token
    public function get(Request $request)
    {
        try {
            $user = $request->user();
            return ResponseFormatter::success($user, 'Get User Data Success');
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Get User Data Failed',
                500,
            );
        }
    }

    // READ ALL
    public function read(Request $request)
    {
        $limit = $request->input('limit');
        $role = $request->input('role', []);
        $search = $request->input('search');

        $user = User::query();

        if ($role) {
            $user->where(function ($query) use ($role) {
                foreach ($role as $value) {
                    $query->orWhere('role', $value);
                }
                return $query;
            });
        }

        if ($search) {
            $user->where(function ($query) use ($search) {
                return $query
                    ->orWhere('nama', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('role', 'like', '%' . $search . '%');
            });
        }

        $total = User::count('id');

        return ResponseFormatter::success(
            [
                'total' => $total,
                'table' => $user->orderBy('created_at')->paginate($limit),
            ],
            'Get Users Successfully'
        );
    }

    // Logout
    public function logout(Request $request)
    {
        try {
            $token = $request->user()->currentAccessToken()->delete();
            return ResponseFormatter::success($token, 'Token Revoked');
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something when wrong',
                    'error' => $error,
                ],
                'Logout Failed',
                500,
            );
        }
    }
}
