<?php

namespace App\Entities;



use App\Entities\Traits\CreatedByRelationshipTrait;
use App\Entities\Traits\StatusesRelationshipTrait;
use App\Entities\Traits\UpdatedByRelationshipTrait;

class Country extends BaseSoftModel implements StatusRelationshipInterface
{
    use CreatedByRelationshipTrait;
    use UpdatedByRelationshipTrait;
    use StatusesRelationshipTrait;

    public $table ='countries';
    protected $primaryKey = 'id';

    const STATUS_KEY = 'COUNTRY_STATUS';

    const STATUS_ACTIVE = 'AC';
    const STATUS_INACTIVE = 'IA';

    protected $fillable = [
        'code',
        'name',
        'status',
        'created_by',
        'updated_by'
    ];

    public function getStatusColumn()
    {
        return 'status';
    }

    public function getStatusTypeValue()
    {
        return Statuses::STATUS_TYPE_COUNTRY;
    }

    public function areas()
    {
        return $this->hasMany(Area::class, 'country_id');
    }
}
