<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushDelivery extends Model
{
    protected $fillable = ['notification_id', 'device_token_id', 'status', 'attempts', 'available_at', 'last_error'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function deviceToken()
    {
        return $this->belongsTo(DeviceToken::class);
    }
}
