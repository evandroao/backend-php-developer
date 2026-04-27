<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Verifica se o usuário autenticado possui uma das roles permitidas.
     *
     * Uso nas rotas: ->middleware('role:ADMIN') ou ->middleware('role:ADMIN,USER')
     *
     * BUG INTENCIONAL: A comparação de roles está invertida (usa != ao invés de ==)
     * O candidato deve identificar e corrigir este erro.
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
                'status' => 401,
                'error' => 'Unauthorized',
            ], 401);
        }

        $userRole = Role::tryFrom($user->role);

        if ($userRole === null) {
            return response()->json([
                'message' => 'Access Denied',
                'status' => 403,
                'error' => 'Forbidden',
            ], 403);
        }
	// verifica rota especifica
        if (!in_array($userRole->value, $roles, true)) {
            return response()->json([
                'message' => 'Access Denied',
                'status' => 403,
                'error' => 'Forbidden',
            ], 403);
        }

        return $next($request);
    }
}
