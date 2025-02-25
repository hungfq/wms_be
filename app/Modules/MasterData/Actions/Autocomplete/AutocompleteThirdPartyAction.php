<?php

namespace App\Modules\MasterData\Actions\Autocomplete;

use App\Entities\ThirdParty;
use App\Modules\MasterData\DTO\Autocompletete\AutocompleteThirdPartyDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class AutocompleteThirdPartyAction
{
    const DEFAULT_PAGE = 1;

    /**
     * handle
     *
     * @param AutocompleteThirdPartyDTO $dto
     */
    public function handle($dto)
    {
        $query = ThirdParty::query()
            ->select([
                'third_party.*',
                'third_party.created_at AS created_dt',
                'countries.name AS country_name',
                'states.country_id',
                'states.name AS state_name',
            ])
            ->join('states', 'states.id', '=', 'third_party.state_id')
            ->join('countries', 'countries.id', '=', 'states.country_id')
            ->where('third_party.cus_id', $dto->cus_id);

        if ($code = $dto->code) {
            $query->where('third_party.code', 'LIKE', "%$code%");
        }

        if ($name = $dto->name) {
            $query->where('third_party.name', 'LIKE', "%$name%");
        }

        if ($address = $dto->address) {
            $query->where('third_party.addr_1', 'LIKE', "%$address%");
        }

        $query->orderBy('third_party.tp_id', 'DESC');

        $limit = $dto->limit ?? ITEM_PER_PAGE;
        $page = $dto->page ?? self::DEFAULT_PAGE;

        $results = $query->simplePaginate($limit);

        return new LengthAwarePaginator($results->items(), null, $limit, $page, ['has_more' => $results->hasMorePages()]);
    }
}
