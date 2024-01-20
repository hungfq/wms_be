<?php

namespace App\Entities;

class LanguageDtl extends BaseModel
{
    public $table = 'language_dtl';

    protected $primaryKey = 'lg_dtl_id';

    protected $fillable = [
        'lg_id',
        'language_code',
        'translate'
    ];

    public function languageDtl()
    {
        return $this->belongsTo(LanguageHdr::class, 'lg_id');
    }
}
