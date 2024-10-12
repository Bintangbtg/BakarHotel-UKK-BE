<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Periksa apakah user sudah login
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Ambil user yang sedang login
        $user = Auth::user();

        // Periksa apakah role user cocok dengan yang diberikan di parameter
        if ($user->role !== $role) {
            return response()->json(['error' => 'Forbidden'], 403);  // Tidak diizinkan
        }

        // Jika role cocok, lanjutkan request
        return $next($request);
    }
}