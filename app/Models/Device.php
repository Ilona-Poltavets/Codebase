<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'uuid',
        'name',
        'platform',
        'app_version',
        'pairing_code_hash',
        'pairing_code_expires_at',
        'last_seen_at',
        'revoked_at',
        'meta',
    ];

    protected $casts = [
        'pairing_code_expires_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'revoked_at' => 'datetime',
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

    public function tokens()
    {
        return $this->hasMany(DeviceToken::class, 'device_id');
    }
}
