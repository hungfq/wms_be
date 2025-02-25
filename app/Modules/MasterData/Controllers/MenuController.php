<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\Menu;
use App\Exceptions\UserException;
use App\Http\Controllers\ApiController;
use App\Libraries\Language;

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

    public function update()
    {
        $type = $this->request->input('type') ?? 'web';

        $menu = Menu::where('type', $type)->first();

        if (!$menu) {
            throw new UserException(Language::translate('Menu not found!'));
        }

        $menu->content = $this->request->input('content');

        $menu->save();

        return $this->responseSuccess();
    }
}
