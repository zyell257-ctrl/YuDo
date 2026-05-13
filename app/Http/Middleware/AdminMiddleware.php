<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Cek apakah user sudah login sebagai admin
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Silakan login terlebih dahulu.'], 401);
            }

            return redirect()->route('login')
                             ->with('error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
