<?php

namespace App\Modules\MasterData\Actions\Language;

use App\Entities\LanguageDtl;
use App\Entities\LanguageHdr;
use App\Exceptions\UserException;
use App\Libraries\Language;

class LanguageCreateAction
{
    public function handle($params = [])
    {
        $language_code = data_get($params, 'language_code', '');

        if (empty($language_code)) {
            return false;
        }

        $data = [
            'message' => data_get($params, 'message', ''),
            'lg_type' => data_get($params, 'lg_type', 'FE')
        ];

        $languages = LanguageHdr::query()
            ->where([
                'message' => data_get($params, 'message', ''),
                'lg_type' => data_get($params, 'lg_type', 'FE')
            ])
            ->first();

        if ($languages) {
            throw new UserException(Language::translate('The message has already been taken.'));
        }

        $language = LanguageHdr::query()->create($data);
        $id = $language->lg_id;

        LanguageDtl::query()
            ->create([
                'lg_id' => $id,
                'language_code' => $language_code,
                'translate' => data_get($params, 'translate', '')
            ]);

        return true;
    }
}