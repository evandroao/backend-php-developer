<?php

namespace App\Http\Controllers;

use App\Http\Requests\EpisodeAverageRequest;
use App\Models\Episode;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="EpisodeController", description="API de gerenciamento de episódios")
 */
class EpisodeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/episodes/average",
     *     summary="Nota média por temporada",
     *     tags={"EpisodeController"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="show_id", in="query", required=true, description="ID do show"),
     *     @OA\Response(response=200, description="Médias calculadas com sucesso"),
     *     @OA\Response(response=404, description="Não há episódios para este show")
     * )
     */
    public function average(EpisodeAverageRequest $request): JsonResponse
    {
        $showId = $request->validated('show_id');

        $hasEpisodes = Episode::where('show_id', $showId)->exists();

        if (!$hasEpisodes) {
            return response()->json([
                'message' => 'No episodes found for this show',
                'status' => 404,
                'error' => 'Not Found',
                'timestamp' => now()->toIso8601String(),
            ], 404);
        }

        $averages = Episode::query()
            ->select('season')
            ->selectRaw('COALESCE(AVG(rating), 0) as average')
            ->where('show_id', $showId)
            ->groupBy('season')
            ->orderBy('season')
            ->get()
            ->map(fn ($row) => [
                'season' => (int) $row->season,
                'average' => (float) round($row->average, 2),
            ]);

        return response()->json($averages);
    }
}
