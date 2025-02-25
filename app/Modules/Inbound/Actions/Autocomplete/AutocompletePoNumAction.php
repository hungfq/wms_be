<?php

namespace App\Modules\Inbound\Actions\Autocomplete;

use App\Entities\PoHdr;
use App\Modules\Inbound\DTO\Autocomplete\AutocompletePoNumDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class AutocompletePoNumAction
{
    const DEFAULT_PAGE = 1;

    /**
     * handle
     *
     * @param AutocompletePoNumDTO $dto
     */
    public function handle($dto)
    {
        $query = PoHdr::query();

        if ($search = $dto->search) {
            $query->where('po_num', 'LIKE', "%{$search}%");
        }

        $query->orderBy('created_at', 'DESC');

        $limit = $dto->limit ?? ITEM_PER_PAGE;
        $page = $dto->page ?? self::DEFAULT_PAGE;

        $results = $query->simplePaginate($limit);

        return new LengthAwarePaginator($results->items(), null, $limit, $page, ['has_more' => $results->hasMorePages()]);
    }
}
