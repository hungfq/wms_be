<?php

namespace App\Databases;

use App\Libraries\Data;
use Illuminate\Database\Eloquent\Builder;

class BaseBuilder extends Builder
{
    public function update(array $values)
    {
        $currentUser = Data::getCurrentUser();
        $updateByColumn = $this->model->getQualifiedUpdatedByColumn();

        if (!isset($values[$updateByColumn]) && $currentUser) {
            $values[$this->model->getQualifiedUpdatedByColumn()] = $currentUser->id;
        }
        return parent::update($values);
    }
}