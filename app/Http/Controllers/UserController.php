<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserListRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="UserController", description="API de gerenciamento de usuários")
 */
class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Lista usuários com paginação",
     *     tags={"UserController"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="username", in="query", required=false, description="Nome do usuário para filtro"),
     *     @OA\Parameter(name="page", in="query", required=false, description="Número da página (inicia em 0)"),
     *     @OA\Parameter(name="size", in="query", required=false, description="Quantidade de registros por página"),
     *     @OA\Parameter(name="sortField", in="query", required=false, description="Campo para ordenação"),
     *     @OA\Parameter(name="sortOrder", in="query", required=false, description="Direção da ordenação (ASC ou DESC)"),
     *     @OA\Response(response=200, description="Listagem realizada com sucesso")
     * )
     */
    public function index(UserListRequest $request): JsonResponse
    {
        $username = $request->validated('username', '');
        $page = (int) $request->validated('page', 0);
        $size = (int) $request->validated('size', 10);
        $sortField = $request->validated('sortField', 'id');
        $sortOrder = $request->validated('sortOrder', 'ASC');

        $paginator = $this->userService->findByUsernameContaining($username, $page, $size, $sortField, $sortOrder);

        $paginator->getCollection()->transform(fn ($user) => new UserResource($user));

        return response()->json(PaginationHelper::formatPageResult($paginator));
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Consulta um usuário pelo id",
     *     tags={"UserController"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID do usuário"),
     *     @OA\Response(response=200, description="Consulta realizada com sucesso"),
     *     @OA\Response(response=404, description="Usuário não encontrado")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $user = $this->userService->findById($id);
        return response()->json(new UserResource($user));
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Registra um usuário",
     *     tags={"UserController"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserCreateRequest")),
     *     @OA\Response(response=201, description="Usuário cadastrado com sucesso"),
     *     @OA\Response(response=409, description="Username já existe")
     * )
     */
    public function store(UserCreateRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());
        return response()->json(new UserResource($user), 201)
            ->header('Location', '/api/users/' . $user->id);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Atualiza um usuário",
     *     tags={"UserController"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID do usuário"),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserUpdateRequest")),
     *     @OA\Response(response=200, description="Usuário atualizado com sucesso")
     * )
     */
    public function update(UserUpdateRequest $request, string $id): JsonResponse
    {
        $user = $this->userService->update($id, $request->validated());
        return response()->json(new UserResource($user));
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Remove um usuário",
     *     tags={"UserController"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID do usuário"),
     *     @OA\Response(response=204, description="Usuário removido com sucesso")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $this->userService->delete($id);
        return response()->json(null, 204);
    }
}
