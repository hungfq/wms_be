<?php

namespace App\Modules\Notification\Transformers;

use League\Fractal\TransformerAbstract;

class NotificationViewTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            '_id' => $model->id,
            'title' => $model->title,
            'message' => $model->message,
            'isRead' => (bool)$model->is_read,
            'createdAt' => $model->created_at,
            'created_at' => $model->created_at,
            'created_by_name' => data_get($model, 'createdBy.name'),
            'updatedAt' => $model->updated_at,
            'updated_at' => $model->updated_at,
            'updated_by_name' => data_get($model, 'updatedBy.name'),
        ];
    }
}