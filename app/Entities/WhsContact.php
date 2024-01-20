<?php

namespace App\Entities;

class WhsContact extends BaseSoftModel
{
    public $table ='whs_contact';

    protected $primaryKey = 'cont_id';

    protected $guarded = ['id'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'whs_id');
    }
}
