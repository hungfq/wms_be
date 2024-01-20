<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\Menu;
use App\Http\Controllers\ApiController;

class MenuController extends ApiController
{
    public function index()
    {
        $type = $this->request->input('type') ?? 'web';

        $menu = Menu::select(['id', 'content'])
            ->where('type', $type)
            ->first();

        $menu = $menu ?? [];

        return ['data' => $menu];
    }
}
