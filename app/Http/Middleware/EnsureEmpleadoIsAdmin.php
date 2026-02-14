<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureEmpleadoIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('empleados')->user();
        if (!$user || strtolower($user->Cargo ?? '') !== 'administrador') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return redirect('/login');
        }
        return $next($request);
    }
}
