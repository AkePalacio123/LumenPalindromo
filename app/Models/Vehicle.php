<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = ['company_id', 'unit_number', 'plate', 'make', 'model', 'year', 'capacity_tons', 'status', 'last_location'];
}
