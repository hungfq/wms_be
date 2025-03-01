<?php

namespace App\Modules\MasterData\Controllers;

use App\Entities\LanguageHdr;
use App\Http\Controllers\ApiController;
use App\Libraries\Language;
use App\Modules\MasterData\Actions\Language\LanguageCreateAction;
use App\Modules\MasterData\Actions\Language\LanguageDeleteAction;
use App\Modules\MasterData\Actions\Language\LanguageUpdateAction;
use App\Modules\MasterData\Actions\Language\LanguageViewAction;
use App\Modules\MasterData\Transformers\Language\LanguageTransformer;
use App\Modules\MasterData\Validators\Language\LanguageCreateValidator;
use App\Modules\MasterData\Validators\Language\LanguageUpdateValidator;
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

    public function languageType()
    {
        return [
            'data' => [
                [
                    'code' => 'FE',
                    'name' => 'User',
                ],
                [
                    'code' => 'BE',
                    'name' => 'System',
                ]
            ]
        ];
    }

    public function view(LanguageViewAction $action)
    {
        $languages = $action->handle($this->request->all());

        return $this->response->paginator($languages, new LanguageTransformer());
    }

    public function store(LanguageCreateValidator $validator, LanguageCreateAction $action)
    {
        $validator->validate($this->request->all());

        DB::transaction(function () use ($action) {
            $action->handle($this->request->all());
        });

        return $this->responseSuccess(Language::translate('Create Language Successfully!'));
    }

    public function update($lgId, LanguageUpdateValidator $validator, LanguageUpdateAction $action)
    {
        $this->request->merge([
            'lg_id' => $lgId,
        ]);

        $validator->validate($this->request->all());

        DB::transaction(function () use ($action) {
            $action->handle($this->request->all());
        });

        return $this->responseSuccess(Language::translate('Update Language Successfully!'));
    }

    public function delete(LanguageDeleteAction $action)
    {
        DB::transaction(function () use ($action) {
            $action->handle($this->request->all());
        });

        return $this->responseSuccess(Language::translate('Delete Language Successfully!'));
    }
}
