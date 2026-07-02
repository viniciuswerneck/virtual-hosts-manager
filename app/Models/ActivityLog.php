<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'action',
        'description',
        'subject_type',
        'subject_id',
        'ip',
    ];

    public function subject()
    {
        return $this->morphTo();
    }
}
