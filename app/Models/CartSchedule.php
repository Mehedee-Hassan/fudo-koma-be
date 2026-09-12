<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartSchedule extends Model
{
    protected $fillable = ['cart_id', 'day_of_week', 'opens_at', 'closes_at', 'timezone', 'address', 'specific_date', 'is_active', 'latitude', 'longitude'];
}
