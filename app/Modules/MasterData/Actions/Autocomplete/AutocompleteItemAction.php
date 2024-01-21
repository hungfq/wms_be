<?php

namespace App\Modules\MasterData\Actions\Autocomplete;

use App\Entities\Item;
use App\Modules\MasterData\DTO\Autocompletete\AutocompleteItemDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class AutocompleteItemAction
{
    const DEFAULT_PAGE = 1;

    /**
     * handle
     *
     * @param AutocompleteItemDTO $dto
     */
    public function handle($dto)
    {
        $query = Item::query();

        if ($search = $dto->search) {
            $query->where('sku', 'LIKE', "%{$search}%");
        }

        $query->orderBy('created_at', 'DESC');

        $limit = $dto->limit ?? ITEM_PER_PAGE;
        $page = $dto->page ?? self::DEFAULT_PAGE;

        $results = $query->simplePaginate($limit);

        return new LengthAwarePaginator($results->items(), null, $limit, $page, ['has_more' => $results->hasMorePages()]);
    }
}
