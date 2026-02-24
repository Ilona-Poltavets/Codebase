<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeSession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'device_id',
        'started_at',
        'ended_at',
        'total_seconds',
        'status',
        'timezone',
        'meta',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'meta' => 'array',
    ];

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

    public function segments()
    {
        return $this->hasMany(TimeSegment::class, 'session_id');
    }

    public function screenshots()
    {
        return $this->hasMany(Screenshot::class, 'session_id');
    }
}
