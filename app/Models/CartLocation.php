<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartLocation extends Model
{
    protected $fillable = ['cart_id', 'latitude', 'longitude', 'address'];

    protected function casts(): array
    {
        return ['latitude' => 'float', 'longitude' => 'float'];
    }
}
