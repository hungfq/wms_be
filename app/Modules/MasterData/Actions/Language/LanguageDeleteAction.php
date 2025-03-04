<?php

namespace App\Modules\MasterData\Actions\Language;

use App\Entities\LanguageDtl;
use App\Entities\LanguageHdr;

class LanguageDeleteAction
{
    public function handle($ids)
    {
        if (!$ids) {
            return false;
        }

        LanguageDtl::query()->whereIn('lg_id', $ids)->delete();

        return LanguageHdr::query()->whereIn('lg_id', $ids)->delete();
    }
}