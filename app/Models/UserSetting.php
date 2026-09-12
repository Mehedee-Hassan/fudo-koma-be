<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = ['user_id', 'push_enabled', 'nearby_enabled', 'updates_enabled', 'radius_meters'];

    protected function casts(): array
    {
        return ['push_enabled' => 'boolean', 'nearby_enabled' => 'boolean', 'updates_enabled' => 'boolean'];
    }
}
