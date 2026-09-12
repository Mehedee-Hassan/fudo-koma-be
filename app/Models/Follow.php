<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $fillable = ['user_id', 'cart_id', 'is_following'];

    protected function casts(): array
    {
        return ['is_following' => 'boolean'];
    }
}
