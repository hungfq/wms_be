<?php

namespace App\Modules\MasterData\Actions\Language;

use App\Entities\LanguageDtl;

class LanguageUpdateAction
{
    public function handle($params = [])
    {
        $language_code = data_get($params, 'language_code', '');
        $lg_dtl_id = data_get($params, 'lg_dtl_id', '');
        $id = data_get($params, 'lg_id', '');

        if (empty($language_code)) {
            return false;
        }

        if ($lg_dtl_id) {
            $languageDtl = LanguageDtl::query()->find($lg_dtl_id);
            if ($languageDtl) {
                $languageDtl->update([
                    'language_code' => $language_code,
                    'translate' => data_get($params, 'translate', '')
                ]);
            }
        } else {
            LanguageDtl::query()->create([
                'lg_id' => $id,
                'language_code' => $language_code,
                'translate' => data_get($params, 'translate', '')
            ]);
        }

        return true;
    }
}