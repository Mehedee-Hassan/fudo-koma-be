<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['reporter_id', 'target_type', 'target_id', 'reason', 'note', 'status', 'resolution_note', 'resolved_by', 'resolved_at'];
}
