<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartLocation extends Model
{
    protected $fillable = ['cart_id', 'latitude', 'longitude', 'address'];

    protected static function booted(): void
    {
        static::saving(function (CartLocation $location): void {
            // A save represents a current-position submission, never a history insert.
            // Use server receipt time; client clocks cannot make a location look fresh.
            $location->recorded_at = now();
        });
    }

    protected function casts(): array
    {
        return ['latitude' => 'float', 'longitude' => 'float', 'recorded_at' => 'datetime'];
    }
}
