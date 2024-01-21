<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\MasterData\Actions\Autocomplete\AutocompleteItemAction;
use App\Modules\MasterData\DTO\Autocompletete\AutocompleteItemDTO;
use App\Modules\MasterData\Transformers\Autocomplete\AutocompleteItemTransformer;

class AutocompleteController extends ApiController
{
    public function items(AutocompleteItemAction $action, AutocompleteItemTransformer $transformer)
    {
        $results = $action->handle(AutocompleteItemDTO::fromRequest());

        return $this->response->paginator($results, $transformer)->addMeta('has_more', $results->has_more);
    }
}
