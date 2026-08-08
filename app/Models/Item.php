<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    /** In stock and bookable. */
    public const STATUS_AVAILABLE = 'available';

    /** Out of stock. Derived from the quantity, never set by hand. */
    public const STATUS_EMPTY = 'empty';

    /** Withdrawn from service by an admin. Sticky: stock level does not undo it. */
    public const STATUS_OUT_OF_SERVICE = 'not available';

    protected $fillable = [
        'category_id', 
        'unit_id', 
        'name',
        'brand', 
        'serial_number', 
        'photo', 
        'quantity', 
        'status',
        'description',
        
    ];

    /**
     * Availability follows the stock level automatically.
     *
     * Booking approvals draw stock down and returns put it back; keeping the
     * status in sync by hand at each of those call sites meant returns restocked
     * an item but left it reading "empty" forever. Deriving it here means every
     * write path — approve, return, expiry sweep, manual edit, seeder — stays
     * correct without having to remember.
     *
     * "not available" is the one status a person owns: it marks an item pulled
     * out of service, so stock movements must not silently override it.
     */
    protected static function booted(): void
    {
        static::saving(function (Item $item) {
            if ($item->status === self::STATUS_OUT_OF_SERVICE) {
                return;
            }

            $item->status = ((int) $item->quantity) <= 0
                ? self::STATUS_EMPTY
                : self::STATUS_AVAILABLE;
        });
    }

    /** Whether this item can currently be booked. */
    public function isBookable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE && $this->quantity > 0;
    }

    public function rules()
    {
        return [
            'serial_number' => 'unique:items',
            'quantity' => function ($attribute, $value, $fail) {
                if ($this->serial_number && $value != 1) {
                    $fail('If the item has a serial number, the quantity must be 1.');
                }
            },
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    protected static function newFactory()
    {
        return \Database\Factories\ItemFactory::new();
    }

    protected $primaryKey = 'id';
}
