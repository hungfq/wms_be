<?php

namespace App\Modules\Template\Actions;

use App\Exceptions\UserException;
use App\Modules\Template\DTO\TemplateDownloadDTO;

class TemplateDownloadAction
{
    /**
     * @param TemplateDownloadDTO $dto
     * @return string
     * @throws UserException
     */
    public function handle($dto)
    {
        $type = $dto->type;
        $path = "templates/{$type}Template.xlsx";

        $exists = file_exists(public_path($path));
        if (!$exists) {
//            throw new UserException('File not found');
            throw new UserException('Tệp mẫu không tồn tại trong hệ thống!', 400);
        }

        return $path;
    }
}