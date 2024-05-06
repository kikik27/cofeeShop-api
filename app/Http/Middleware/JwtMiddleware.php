<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Mencoba untuk mengautentikasi pengguna menggunakan token JWT
            $user = JWTAuth::parseToken()->authenticate();

            // Jika pengguna tidak terotentikasi, kembalikan respons JSON dengan status 401 (Unauthorized)
            if (!$user) {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
            }
        } catch (Exception $e) {
            // Menangani kesalahan yang mungkin terjadi selama proses autentikasi

            // Jika token tidak valid, kembalikan respons JSON dengan status 401 (Unauthorized) dan pesan "Token is Invalid"
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['status' => false, 'message' => 'Token is Invalid'], 401);
            }
            // Jika token telah kedaluwarsa, kembalikan respons JSON dengan status 401 (Unauthorized) dan pesan "Token is Expired"
            else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['status' => false, 'message' => 'Token is Expired'], 401);
            }
            // Jika terjadi kesalahan lain selama autentikasi, kembalikan respons JSON dengan status 401 (Unauthorized) dan pesan "Authorization Token not found"
            else {
                return response()->json(['status' => false, 'message' => 'Authorization Token not found'], 401);
            }
        }

        // Jika autentikasi berhasil, lanjutkan ke middleware atau handler berikutnya
        return $next($request);

    }
}
