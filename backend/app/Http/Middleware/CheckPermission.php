<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }
        if (!$user->hasPermission($permission)) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => "Se requiere el permiso: {$permission}"
            ], 403);
        }

        return $next($request);
    }
}