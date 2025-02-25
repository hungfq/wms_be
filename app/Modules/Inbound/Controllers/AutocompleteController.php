<?php

namespace App\Modules\Inbound\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\Inbound\Actions\Autocomplete\AutocompletePoNumAction;
use App\Modules\Inbound\Actions\Autocomplete\AutocompleteVendorAction;
use App\Modules\Inbound\DTO\Autocomplete\AutocompletePoNumDTO;
use App\Modules\Inbound\DTO\Autocomplete\AutocompleteVendorDTO;
use App\Modules\Inbound\Transformers\Autocomplete\AutocompletePoNumTransformer;
use App\Modules\Inbound\Transformers\Autocomplete\AutocompleteVendorTransformer;

class AutocompleteController extends ApiController
{
    public function poNum(AutocompletePoNumAction $action, AutocompletePoNumTransformer $transformer)
    {
        $results = $action->handle(
            AutocompletePoNumDTO::fromRequest()
        );

        return $this->response->paginator($results, $transformer)->addMeta('has_more', $results->has_more);
    }

    public function vendor(AutocompleteVendorAction $action, AutocompleteVendorTransformer $transformer)
    {
        $results = $action->handle(
            AutocompleteVendorDTO::fromRequest()
        );

        return $this->response->paginator($results, $transformer)->addMeta('has_more', $results->has_more);
    }
}
