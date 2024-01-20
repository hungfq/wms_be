<?php

namespace App\Modules\Template\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\Template\Actions\TemplateDownloadAction;
use App\Modules\Template\DTO\TemplateDownloadDTO;

class TemplateController extends ApiController
{
    public function download(TemplateDownloadAction $action, TemplateDownloadDTO $dto)
    {
        $result = $action->handle($dto::fromRequest());

        return response()->download($result);
    }
}