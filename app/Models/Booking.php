<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'quantity',
        'start_date',
        'end_date',
        'jo_number',
        'note',
        'status'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // withTrashed: deactivating a borrower must not blank out their booking history
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function usage()
    {
        return $this->hasOne(Usage::class);
    }

    protected static function newFactory()
    {
        return \Database\Factories\BookingFactory::new();
    }

    protected $primaryKey = 'id';
}
