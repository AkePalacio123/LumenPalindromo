<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = ['company_id', 'trip_number', 'origin', 'destination', 'cargo', 'departure_at', 'eta', 'status', 'vehicle_id', 'driver_id', 'distance_km', 'notes'];
}
