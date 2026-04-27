<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(name="AuthController", description="API de controle autenticações")
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login de um usuário",
     *     tags={"AuthController"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(response=200, description="Login realizado com sucesso",
     *         @OA\JsonContent(@OA\Property(property="token", type="string"))
     *     ),
     *     @OA\Response(response=401, description="Credenciais inválidas")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ];

        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !$user->enabled) {
            return response()->json([
                'message' => 'Bad credentials',
                'status' => 401,
                'error' => 'Unauthorized',
            ], 401);
        }

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Bad credentials',
                'status' => 401,
                'error' => 'Unauthorized',
            ], 401);
        }

        return response()->json(['token' => $token]);
    }
}
