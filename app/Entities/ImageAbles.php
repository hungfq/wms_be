<?php

namespace App\Entities;

use App\Traits\HasImageTrait;
use Illuminate\Database\Eloquent\Model;

class ImageAbles extends Model
{
    use HasImageTrait;

    const UPLOAD_DIR = PoHdr::FOLDER_ASN;

    protected $table = 'imageables';

    protected $guarded = ['id'];

    public function poHdr()
    {
        return $this->belongsTo(PoHdr::class, 'imageables_id', 'po_hdr_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
