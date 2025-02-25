<?php

namespace App\Modules\MasterData\Actions\Autocomplete;

use App\Entities\Location;
use App\Modules\MasterData\DTO\Autocompletete\AutocompleteItemDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class AutocompleteLocationAction
{
    const DEFAULT_PAGE = 1;

    /**
     * handle
     *
     * @param AutocompleteItemDTO $dto
     */
    public function handle($dto)
    {
        $query = Location::query();

        if ($dto->whs_id) {
            $query->where('locations.whs_id', $dto->whs_id);
        }

        if ($search = $dto->search) {
            $query->where('loc_code', 'LIKE', "%{$search}%");
        }

        $query->orderBy('loc_code', 'ASC');

        $limit = $dto->limit ?? ITEM_PER_PAGE;
        $page = $dto->page ?? self::DEFAULT_PAGE;

        $results = $query->simplePaginate($limit);

        return new LengthAwarePaginator($results->items(), null, $limit, $page, ['has_more' => $results->hasMorePages()]);
    }
}
