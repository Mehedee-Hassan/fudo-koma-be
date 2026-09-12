<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public static function valueOf(string $key, mixed $default): mixed
    {
        return static::find($key)?->value ?? $default;
    }
}
