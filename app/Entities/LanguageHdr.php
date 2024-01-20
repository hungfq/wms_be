<?php

namespace App\Entities;

class LanguageHdr extends BaseModel
{
    public $table = 'languages';

    protected $primaryKey = 'lg_id';

    protected $fillable = [
        'message',
        'lg_type'
    ];

    public function languageDtl()
    {
        return $this->hasMany(LanguageDtl::class, 'lg_id');
    }
}
