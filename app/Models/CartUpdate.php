<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartUpdate extends Model
{
    protected $fillable = ['cart_id', 'title', 'body', 'fanout_at', 'type'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}
