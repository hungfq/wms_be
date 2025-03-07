<?php

namespace App\Modules\MasterData\Actions\Uom;

use App\Entities\Uom;

class UomShowAction
{
    public function handle($id)
    {
        return Uom::query()->findOrFail($id);
    }
}
