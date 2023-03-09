<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\StoreRequest as StoreAuthRequest;
use App\Http\Requests\Auth\AuthLoginRequest as LoginRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private User $model
    ){}

    public function register(StoreAuthRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->model->create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'password' => Hash::make($request->password)
            ]);
            $token = $data->createToken('auth_sanctum')->plainTextToken;
            DB::commit();
            return response()->json([
                'data' => $data,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], 200);
        } catch(ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch(Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json([
                'errors' => 'Proses data gagal, silahka coba lagi'
            ]);
        }
    }

    public function login(LoginRequest $request)
    {
        DB::beginTransaction();
        try {
            if (!Auth::attempt($request->all())) throw new Exception('gagal login');
        
            $data = $this->model->where('email', $request->email)->first();
            $token = $data->createToken($data->name)->plainTextToken;
            DB::commit();
            return response()->json([
                'data' => $data,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], 200);
        } catch(ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch(Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json([
                'errors' => 'Proses data gagal, silahka coba lagi'
            ]);
        }
    }

    public function logout(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->user()->currentAccessToken()->delete();
            
            DB::commit();
            return response()->json([
                'message' => 'User berhasil dihapus'
            ], 200);
        } catch(ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch(Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json([
                'errors' => 'Proses data gagal, silahka coba lagi'
            ]);
        }
    }
}
