<?php

namespace App\Modules\MasterData\Actions\Language;

use App\Entities\LanguageHdr;
use App\Libraries\Helpers;

class LanguageViewAction
{
    public function handle($input = [])
    {
        $query = LanguageHdr::query()
            ->select([
                'languages.lg_id',
                'languages.message',
                'languages.created_at',
                'languages.updated_at',
                'dtl.lg_dtl_id',
                'dtl.language_code',
                'dtl.translate'
            ])
            ->leftJoin('language_dtl AS dtl', function ($join) use ($input) {
                $join->on('dtl.lg_id', '=', 'languages.lg_id');
                if (isset($input['language_code'])) {
                    $join->where('dtl.language_code', '=', $input['language_code']);
                }
            });

        if ($input['lg_type'] ?? null) {
            $query->where('languages.lg_type', $input['lg_type']);
        }

        if ($input['message'] ?? null) {
            $query->where('languages.message', 'like', '%' . $input['message'] . '%');
        }

        if ($input['translate'] ?? null) {
            $query->where('dtl.translate', 'like', '%' . $input['translate'] . '%');
        }

        Helpers::sortBuilder($query, $input);

        return $query->paginate($input['limit'] ?? ITEM_PER_PAGE);
    }
}