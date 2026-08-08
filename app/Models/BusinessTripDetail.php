<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessTripDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_document_id',
        'driver_name','vehicle_plate_no',
        'speedometer_begin','speedometer_end',
        'total_mileage_km',
        'trip_date','time_out','time_in',
        'purpose','checked_by','noted_by'
    ];

    protected $casts = [
        'trip_date' => 'date',
        'time_out'  => 'datetime:H:i',
        'time_in'   => 'datetime:H:i',
    ];

    public function requestDocument()
    {
        return $this->belongsTo(RequestDocument::class);
    }
}