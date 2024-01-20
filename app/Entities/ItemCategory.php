<?php

namespace App\Entities;


class ItemCategory extends BaseModel
{
    const STATUS_KEY = 'ITEM_CATEGORY_STATUS';

    const STATUS_ACTIVE = 'AC';
    const STATUS_INACTIVE = 'IA';

    protected $fillable = [
        'code',
        'name',
        'bu_id',
        'description',
        'status',
        'created_by',
        'updated_by'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function generateCateCode()
    {
        $num = 1;
        $lastCate = ItemCategory::query()->orderBy('id', 'desc')->first();
        if ($id = data_get($lastCate, 'id')) {
            $num = $id + 1;
        }
        return "CAT_" . $num;
    }
}
