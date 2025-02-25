<?php

namespace App\Modules\User\Transformers;


use League\Fractal\TransformerAbstract;

class UserShowTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            '_id' => $model->id,
            'email' => $model->email,
            'code' => $model->code,
            'name' => $model->name,
            'gender' => $model->gender,
            'status' => $model->status,
            'picture' => $model->picture,
            'created_at' => $model->created_at,
            'created_by_name' => data_get($model, 'created_by_name'),
            'updated_at' => $model->updated_at,
            'updated_by_name' => data_get($model, 'updated_by_name'),
            'total_advisor_topic' => $model->advisorTopics->count(),
            'total_critical_topic' => $model->criticalTopics->count(),
        ];
    }
}
