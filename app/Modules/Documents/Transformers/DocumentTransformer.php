<?php

namespace App\Modules\Documents\Transformers;

use League\Fractal\TransformerAbstract;

class DocumentTransformer extends TransformerAbstract
{
    public function transform($document)
    {
        return [
            'id' => $document->id,
            'owner' => $document->owner,
            'path' => $document->path,
            'des' => $document->des,
            'title' => $document->title,
            'file_name' => $document->file_name,
            'file_extension' => $document->file_extension,
            'type' => $document->type,
            'size' => $document->size,
            'created_by' => $document->created_by,
//            'created_by_name' => data_get($document, 'createdBy.name'),
            'updated_by' => $document->updated_by,
//            'updated_by_name' => data_get($document, 'updatedBy.name'),
            'created_at' => $document->created_at,
            'updated_at' => $document->updated_at,
        ];
    }
}
