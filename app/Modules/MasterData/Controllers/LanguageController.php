<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\LanguageHdr;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;

class LanguageController extends ApiController
{
    public function getAll()
    {
        $input = $this->request->all();

        $languages = LanguageHdr::query()
            ->select([
                "languages.message",
                DB::raw("IF(dtl.translate = '', NULL, dtl.translate) AS translate")
            ])
            ->leftJoin('language_dtl AS dtl', function ($join) use ($input) {
                $join->on('dtl.lg_id', '=', 'languages.lg_id');
                if (isset($input['language_code'])) {
                    $join->where('dtl.language_code', '=', $input['language_code']);
                }
            })
            ->where('languages.lg_type', 'FE')
            ->get();

        $data = [];
        if ($languages) {
            foreach ($languages as $language) {
                $data[$language->message] = $language->translate;
            }
        }

        return [
            'data' => $data
        ];
    }
}
