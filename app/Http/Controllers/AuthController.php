<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            //proses validasi inputan
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:admins,email', // Email harus diisi, berupa alamat email yang valid, dan sudah terdaftar di database admins.
                'password' => 'required|min:8' // Password harus diisi dan minimal 8 karakter.
            ]);


            //ketika validasi di atas tidak terpenuhi, maka response akan seperti di bawah dengan status code http 422
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => 'Inputan yang anda masukkan tidak valid', 'erros' => $validator->errors()], 422);
            }

            //mengambil payload inputan user, hanya email dan password saja yang di ambil
            $payload = $request->only('email', 'password');

            //proses pembuatan token dengan menggunakan guard yang di set ke api dengan provider admin dan driver jwt, bisa di check di config/auth.php unutk merubah konfigurasi
            if (!$token = auth()->guard('api')->attempt($payload)) {
                //respose ketika password salah, status code 401 unauthorized
                return response()->json([
                    'status' => false,
                    'message' => 'Password Anda salah'
                ], 401);
            }

            //response ketika proses login berhasil, status code 200 ok
            return response()->json([
                'success' => true,
                'message' => 'login berhasil',
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            //jika ada kesalahan didalam blok try&catch maka blok ini akan terreturn sebagai response
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'user' => $request->user(),
            ], 200);
        } catch (UserNotDefinedException $e) {
            // Tangani jika pengguna tidak ditemukan
            return response()->json([
                'status' => false,
                'message' => 'Pengguna tidak ditemukan'
            ], 404);
        } catch (TokenInvalidException $e) {
            // Tangani jika token tidak valid
            return response()->json([
                'status' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }
    }

}
