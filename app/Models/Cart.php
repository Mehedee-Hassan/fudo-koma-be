<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $attributes = ['status' => 'closed', 'moderation_status' => 'pending', 'is_featured' => false];

    protected $fillable = ['owner_id', 'name', 'description', 'cuisine', 'status', 'moderation_status', 'is_featured'];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean'];
    }

    public function follows()
    {
        return $this->hasMany(Follow::class);
    }

    public function location()
    {
        return $this->hasOne(CartLocation::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function schedules()
    {
        return $this->hasMany(CartSchedule::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function scopeVisible($query)
    {
        return $query->withCount(['follows as followers_count' => fn ($q) => $q->where('is_following', true)])->where('moderation_status', 'approved')->whereHas('owner', fn ($q) => $q->where('is_active', true));
    }
}
