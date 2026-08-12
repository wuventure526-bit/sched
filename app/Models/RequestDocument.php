<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\LiquidationPurpose;
use App\Models\RevolvingFundDetail;
class RequestDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_no',
        'type',
        'user_id',
        'department',
        'name',
        'status',

        'submitted_at',
        'noted_at',
        'approved_at',
        'rejected_at',

        'noted_by',
        'approved_by',
        'rejected_by',
        'rejection_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'noted_at'     => 'datetime',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Owner of the request
    // withTrashed: deactivating an account must not blank out its request history
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    // Liquidation details (only for liquidation type)
    public function liquidationDetail()
    {
        return $this->hasOne(LiquidationDetail::class, 'request_document_id');
    }

    // Payment details (only for payment type)
    public function paymentDetail()
    {
        return $this->hasOne(PaymentDetail::class, 'request_document_id');
    }

    // Line items (shared by both types)
    public function items()
    {
        return $this->hasMany(RequestItem::class, 'request_document_id');
    }

    // Liquidation purposes (only for liquidation type)
    public function purposes()
    {
        return $this->hasMany(LiquidationPurpose::class, 'request_document_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Optional but VERY useful)
    |--------------------------------------------------------------------------
    */

    public function isLiquidation(): bool
    {
        return $this->type === 'liquidation';
    }

    public function isPayment(): bool
    {
        return $this->type === 'payment';
    }
    public function notedByUser()
{
    return $this->belongsTo(\App\Models\User::class, 'noted_by')->withTrashed();
}

public function approvedByUser()
{
    return $this->belongsTo(\App\Models\User::class, 'approved_by')->withTrashed();
}
public function businessTripDetail()
{
    return $this->hasOne(\App\Models\BusinessTripDetail::class, 'request_document_id');
}
public function revolvingFundDetail()
{
    return $this->hasOne(RevolvingFundDetail::class, 'request_document_id');
}

}