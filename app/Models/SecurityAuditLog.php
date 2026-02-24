<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAuditLog extends Model
{
    public $timestamps = false;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'company_id',
        'event_type',
        'ip_address',
        'user_agent',
        'context',
        'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
