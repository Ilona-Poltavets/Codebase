<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySubscription extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'status',
        'current_period_start',
        'current_period_end',
        'cancel_at_period_end',
        'canceled_at',
        'trial_ends_at',
        'metadata',
        'last_synced_at',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'canceled_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function plan()
    {
        return $this->belongsTo(BillingPlan::class, 'plan_id');
    }
}
