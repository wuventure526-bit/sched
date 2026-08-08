<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    protected $fillable = [
        'request_document_id','item_date','particulars','amount'
    ];

    protected $casts = [
        'item_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function requestDocument()
    {
        return $this->belongsTo(RequestDocument::class, 'request_document_id');
    }
}