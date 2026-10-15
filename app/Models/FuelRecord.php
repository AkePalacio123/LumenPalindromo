<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelRecord extends Model
{
    protected $fillable = ['company_id', 'vehicle_id', 'driver_id', 'liters', 'amount', 'odometer_km', 'station', 'refueled_at', 'notes'];
}
