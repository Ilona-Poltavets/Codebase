<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsageEvent extends Model
{
    public $timestamps = false;

    const UPDATED_AT = null;

    protected $fillable = [
        'company_id',
        'user_id',
        'project_id',
        'event_type',
        'resource_type',
        'resource_id',
        'quantity',
        'billable_units',
        'meta',
        'occurred_at',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'occurred_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }
}
