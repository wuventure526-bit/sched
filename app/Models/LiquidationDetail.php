<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiquidationDetail extends Model
{
    protected $fillable = [
        'request_document_id','week_no','form_no','date_from','date_to',
        'cash_advance_amount','cash_advance_date','previous_balance','starting_balance',
        'reimbursement_amount','ending_balance'
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'cash_advance_date' => 'date',
    ];

    public function requestDocument()
    {
        return $this->belongsTo(RequestDocument::class, 'request_document_id');
    }
}