<?php

namespace App\Modules\MasterData\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\MasterData\Actions\Autocomplete\AutocompleteItemAction;
use App\Modules\MasterData\Actions\Autocomplete\AutocompleteLocationAction;
use App\Modules\MasterData\Actions\Autocomplete\AutocompleteThirdPartyAction;
use App\Modules\MasterData\DTO\Autocompletete\AutocompleteItemDTO;
use App\Modules\MasterData\DTO\Autocompletete\AutocompleteThirdPartyDTO;
use App\Modules\MasterData\Transformers\Autocomplete\AutocompleteItemTransformer;
use App\Modules\MasterData\Transformers\Autocomplete\AutocompleteLocationTransformer;
use App\Modules\MasterData\Transformers\Autocomplete\AutocompleteThirdPartyTransformer;

class AutocompleteController extends ApiController
{
    public function items(AutocompleteItemAction $action, AutocompleteItemTransformer $transformer)
    {
        $results = $action->handle(AutocompleteItemDTO::fromRequest());

        return $this->response->paginator($results, $transformer)->addMeta('has_more', $results->has_more);
    }

    public function location(AutocompleteLocationAction $action, AutocompleteLocationTransformer $transformer)
    {
        $results = $action->handle(AutocompleteItemDTO::fromRequest());

        return $this->response->paginator($results, $transformer)->addMeta('has_more', $results->has_more);
    }

    public function thirdParty(AutocompleteThirdPartyAction $action, AutocompleteThirdPartyTransformer $transformer)
    {
        $results = $action->handle(AutocompleteThirdPartyDTO::fromRequest());

        return $this->response->paginator($results, $transformer)->addMeta('has_more', $results->has_more);
    }
}
