<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingPlan extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'price_cents',
        'currency',
        'interval',
        'stripe_price_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(CompanySubscription::class, 'plan_id');
    }
}
