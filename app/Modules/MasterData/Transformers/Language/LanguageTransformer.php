<?php

namespace App\Modules\MasterData\Transformers\Language;

use League\Fractal\TransformerAbstract;

class LanguageTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            'lg_id' => $model->lg_id,
            'lg_dtl_id' => $model->lg_dtl_id,
            'message' => $model->message,
            'language_code' => $model->language_code,
            'translate' => $model->translate,
        ];
    }
}
