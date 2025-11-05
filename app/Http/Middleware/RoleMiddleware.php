<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles  Roles separados por pipe (|)
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        // Separar roles por pipe
        $allowedRoles = explode('|', $roles);

        // Verificar si el usuario tiene alguno de los roles permitidos
        $hasRole = false;
        foreach ($allowedRoles as $role) {
            if ($request->user()->hasRole(trim($role))) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }

        return $next($request);
    }
}
