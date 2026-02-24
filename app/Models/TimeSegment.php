<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeSegment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'session_id',
        'company_id',
        'user_id',
        'device_id',
        'started_at',
        'ended_at',
        'seconds',
        'activity_level',
        'is_idle',
        'app_name',
        'window_title',
        'url',
        'meta',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_idle' => 'boolean',
        'meta' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(TimeSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
