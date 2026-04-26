<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function average(Request $request): JsonResponse
    {
        $showId = $request->query('show_id');

        $episodes = Episode::where('show_id', $showId)
            ->whereNotNull('rating')
            ->get();

        if ($episodes->isEmpty()) {
            return response()->json([
                'message' => 'No episodes found for this show',
                'status' => 404,
                'error' => 'Not Found',
                'timestamp' => now()->toIso8601String(),
            ], 404);
        }

        $averages = $episodes
            ->groupBy('season')
            ->map(function ($seasonEpisodes) {
                $ratings = $seasonEpisodes->pluck('rating')->filter();

                return [
                    'season' => $seasonEpisodes->first()->season,
                    'average' => $ratings->isEmpty() ? 0.0 : (float) round($ratings->avg(), 2),
                ];
            })
            ->values();

        return response()->json($averages);
    }
}
