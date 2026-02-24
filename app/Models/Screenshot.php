<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Screenshot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'session_id',
        'segment_id',
        'company_id',
        'user_id',
        'device_id',
        'disk',
        'path',
        'size_bytes',
        'sha256',
        'width',
        'height',
        'is_blurred',
        'taken_at',
        'meta',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'is_blurred' => 'boolean',
        'meta' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(TimeSession::class, 'session_id');
    }

    public function segment()
    {
        return $this->belongsTo(TimeSegment::class, 'segment_id');
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
