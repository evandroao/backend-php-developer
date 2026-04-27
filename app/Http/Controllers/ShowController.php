<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\ShowCreateRequest;
use App\Http\Requests\ShowListRequest;
use App\Http\Resources\ShowResource;
use App\Services\ShowService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="ShowController", description="API de gerenciamento de shows")
 */
class ShowController extends Controller
{
    private ShowService $showService;

    public function __construct(ShowService $showService)
    {
        $this->showService = $showService;
    }

    /**
     * @OA\Get(
     *     path="/api/shows",
     *     summary="Lista shows com paginação",
     *     tags={"ShowController"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="name", in="query", required=false, description="Nome do show para filtro"),
     *     @OA\Parameter(name="page", in="query", required=false, description="Número da página (inicia em 0)"),
     *     @OA\Parameter(name="size", in="query", required=false, description="Quantidade de registros por página"),
     *     @OA\Response(response=200, description="Listagem realizada com sucesso")
     * )
     */
    public function index(ShowListRequest $request): JsonResponse
    {
        $name = $request->validated('name', '');
        $page = (int) $request->validated('page', 0);
        $size = (int) $request->validated('size', 10);

        $paginator = $this->showService->list($name, $page, $size);

        $paginator->getCollection()->transform(fn ($show) => new ShowResource($show));

        return response()->json(PaginationHelper::formatPageResult($paginator));
    }

    /**
     * @OA\Get(
     *     path="/api/shows/{id}",
     *     summary="Consulta um show pelo id",
     *     tags={"ShowController"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID do show"),
     *     @OA\Response(response=200, description="Consulta realizada com sucesso"),
     *     @OA\Response(response=404, description="Show não encontrado")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $show = $this->showService->findById($id);

        return response()->json(new ShowResource($show));
    }

    /**
     * @OA\Post(
     *     path="/api/shows",
     *     summary="Sincroniza um show da TVMaze",
     *     tags={"ShowController"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ShowCreateRequest")),
     *     @OA\Response(response=201, description="Show sincronizado com sucesso"),
     *     @OA\Response(response=404, description="Show não encontrado na TVMaze")
     * )
     */
    public function store(ShowCreateRequest $request): JsonResponse
    {
        $show = $this->showService->sync($request->input('name'));

        return response()->json(new ShowResource($show), 201)
                         ->header('Location', '/api/shows/' . $show->id);
    }
}
