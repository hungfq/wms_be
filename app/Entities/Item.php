<?php

namespace App\Entities;

use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class Item extends BaseSoftModel implements StatusRelationshipInterface
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;

//    use CustomerRelationshipBelongToTrait;
    use StatusesRelationshipTrait;

    const SERIAL = 1;
    const NON_SERIAL = 0;

    const STATUS_ACTIVE = 'AC';
    const STATUS_INACTIVE = 'IA';

    const DEFAULT_SIZE = 'NA';
    const DEFAULT_COLOR = 'NA';
    const DEFAULT_NUMBER = 0;

    protected $primaryKey = 'item_id';

    protected $guarded = [
        'item_id',
    ];

    public function getStatusColumn()
    {
        return 'status';
    }

    public function getStatusTypeValue()
    {
        return Statuses::STATUS_ITEM_TYPE;
    }

    /**
     * @return mixed
     */
    public function categoryCode()
    {
        return $this->belongsTo(ItemCategory::class, 'cat_code', 'code');
    }

    /**
     * @return mixed
     */
    public function uom()
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }

    public function itemImages()
    {
        return $this->hasMany(ItemImage::class, 'item_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'item_id');
    }

    public function groups()
    {
        return $this->morphToMany(Groups::class, 'groupables');
    }

    public function cartons()
    {
        return $this->hasMany(Carton::class, 'item_id');
    }

    public function itemGroup()
    {
        return $this->belongsTo(ItemGroup::class, 'group_id');
    }

    public function itemStatus()
    {
        return $this->belongsTo(ItemStatus::class, 'item_status_id');
    }

    public function subsidiary()
    {
        return $this->belongsTo(Subsidiary::class, 'subsidiary_id');
    }

    public function itemClass()
    {
        return $this->belongsTo(ItemClass::class, 'item_class_id');
    }
}
