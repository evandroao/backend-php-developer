<?php

namespace App\Services;

use App\Exceptions\ShowNotFoundException;
use App\Integration\Client\RequestService;
use App\Integration\DTO\ShowsRequestDTO;
use App\Models\Episode;
use App\Models\Show;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ShowService
{
    private RequestService $requestService;

    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    public function sync(string $showName): Show
    {
        /** @var ShowsRequestDTO|null $dto */
        $dto = $this->requestService->getShow($showName);

        if (!$dto) {
            throw new ShowNotFoundException($showName);
        }

        return DB::transaction(function () use ($dto) {
            $show = Show::firstOrNew(['id_integration' => $dto->id]);

            $show->fill([
                'name' => $dto->name,
                'type' => $dto->type,
                'language' => $dto->language,
                'status' => $dto->status,
                'runtime' => $dto->runtime,
                'average_runtime' => $dto->averageRuntime,
                'official_site' => $dto->officialSite,
                'rating' => $dto->rating?->average,
                'summary' => $dto->summary,
            ]);
            $show->save();

            $syncedIds = [];
            foreach ($dto->episodes as $episodeDto) {
                Episode::updateOrCreate(
                    ['id_integration' => $episodeDto->id],
                    [
                        'show_id' => $show->id,
                        'name' => $episodeDto->name,
                        'season' => $episodeDto->season,
                        'number' => $episodeDto->number,
                        'type' => $episodeDto->type,
                        'airdate' => $episodeDto->airdate,
                        'airtime' => $episodeDto->airtime,
                        'airstamp' => $episodeDto->airstamp,
                        'runtime' => $episodeDto->runtime,
                        'rating' => $episodeDto->rating?->average,
                        'summary' => $episodeDto->summary,
                    ]
                );
                $syncedIds[] = $episodeDto->id;
            }

            if (!empty($syncedIds)) {
                Episode::where('show_id', $show->id)
                    ->whereNotIn('id_integration', $syncedIds)
                    ->delete();
            }

            return $show;
        });
    }

    public function list(string $name = '', int $page = 0, int $size = 10): LengthAwarePaginator
    {
        return Show::where('name', 'ILIKE', "%{$name}%")
            ->orderBy('name')
            ->paginate($size, ['*'], 'page', $page + 1);
    }

    public function findById(string $id): Show
    {
        return Show::findOrFail($id);
    }
}
