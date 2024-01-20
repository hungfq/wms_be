<?php

namespace App\Modules\MasterData\Transformers\Dropdown;

use League\Fractal\TransformerAbstract;

class ContainerTypeTransformer extends TransformerAbstract
{
    public function transform($containerType)
    {
        return [
            'container_type_id' => $containerType->id,
            'code' => $containerType->code,
            'name' => $containerType->name,
            'description' => $containerType->description,
        ];
    }
}
