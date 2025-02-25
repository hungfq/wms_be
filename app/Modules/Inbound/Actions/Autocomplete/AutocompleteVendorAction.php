<?php

namespace App\Modules\Inbound\Actions\Autocomplete;

use App\Entities\Vendor;
use App\Modules\Inbound\DTO\Autocomplete\AutocompleteVendorDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class AutocompleteVendorAction
{
    const DEFAULT_PAGE = 1;

    /**
     * handle
     *
     * @param AutocompleteVendorDTO $dto
     */
    public function handle($dto)
    {
        $query = Vendor::query();

        if ($code = $dto->code) {
            $query->where('code', 'LIKE', "%{$code}%");
        }

        if ($name = $dto->name) {
            $query->where('name', 'LIKE', "%{$name}%");
        }

        $query->orderBy('id', 'DESC');

        $limit = $dto->limit ?? ITEM_PER_PAGE;
        $page = $dto->page ?? self::DEFAULT_PAGE;

        $results = $query->simplePaginate($limit);

        return new LengthAwarePaginator($results->items(), null, $limit, $page, ['has_more' => $results->hasMorePages()]);
    }
}
