<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $fillable = [
        'request_document_id','payable_to','address','date','total_amount'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function requestDocument()
    {
        return $this->belongsTo(RequestDocument::class, 'request_document_id');
    }
}