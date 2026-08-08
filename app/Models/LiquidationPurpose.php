<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiquidationPurpose extends Model
{
    protected $fillable = [
        'request_document_id','purpose','other_text'
    ];

    public function requestDocument()
    {
        return $this->belongsTo(RequestDocument::class, 'request_document_id');
    }
}