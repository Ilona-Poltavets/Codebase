<?php

namespace App\Support;

use App\Models\BillingPlan;
use App\Models\Company;
use App\Models\CompanySubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionSyncService
{
    public function __construct(private readonly StripeClient $stripe)
    {
    }

    /**
     * @param array<string, mixed> $session
     */
    public function syncFromCheckoutSession(array $session): ?CompanySubscription
    {
        $companyId = (int) data_get($session, 'metadata.company_id', 0);
        $company = $companyId > 0 ? Company::find($companyId) : null;

        if (! $company) {
            $stripeCustomerId = (string) data_get($session, 'customer', '');
            $company = Company::where('stripe_customer_id', $stripeCustomerId)->first();
        }

        if (! $company) {
            return null;
        }

        $stripeCustomerId = (string) data_get($session, 'customer', '');
        if ($stripeCustomerId !== '' && $company->stripe_customer_id !== $stripeCustomerId) {
            $company->stripe_customer_id = $stripeCustomerId;
            $company->save();
        }

        $subscriptionId = (string) data_get($session, 'subscription', '');
        if ($subscriptionId === '') {
            return null;
        }

        return $this->syncByStripeSubscriptionId($subscriptionId, $company);
    }

    public function syncByStripeSubscriptionId(string $stripeSubscriptionId, ?Company $presetCompany = null): ?CompanySubscription
    {
        $subscription = $this->stripe->retrieveSubscription($stripeSubscriptionId);

        return $this->syncFromStripePayload($subscription, $presetCompany);
    }

    /**
     * @param array<string, mixed> $subscription
     */
    public function syncFromStripePayload(array $subscription, ?Company $presetCompany = null): ?CompanySubscription
    {
        $subscriptionId = (string) data_get($subscription, 'id', '');
        if ($subscriptionId === '') {
            return null;
        }

        $stripeCustomerId = (string) data_get($subscription, 'customer', '');
        $priceId = (string) data_get($subscription, 'items.data.0.price.id', '');

        $company = $presetCompany;
        if (! $company && $stripeCustomerId !== '') {
            $company = Company::where('stripe_customer_id', $stripeCustomerId)->first();
        }

        if (! $company) {
            $companyId = (int) data_get($subscription, 'metadata.company_id', 0);
            if ($companyId > 0) {
                $company = Company::find($companyId);
            }
        }

        if (! $company) {
            return null;
        }

        $plan = null;
        if ($priceId !== '') {
            $plan = BillingPlan::where('stripe_price_id', $priceId)->first();
        }

        if (! $plan) {
            $planId = (int) data_get($subscription, 'metadata.plan_id', 0);
            if ($planId > 0) {
                $plan = BillingPlan::find($planId);
            }
        }

        $status = (string) data_get($subscription, 'status', 'incomplete');
        $periodStart = data_get($subscription, 'current_period_start');
        $periodEnd = data_get($subscription, 'current_period_end');
        $cancelAt = data_get($subscription, 'canceled_at');
        $trialEnd = data_get($subscription, 'trial_end');

        return DB::transaction(function () use (
            $company,
            $plan,
            $subscriptionId,
            $stripeCustomerId,
            $status,
            $periodStart,
            $periodEnd,
            $cancelAt,
            $trialEnd,
            $subscription
        ) {
            if ($stripeCustomerId !== '' && $company->stripe_customer_id !== $stripeCustomerId) {
                $company->stripe_customer_id = $stripeCustomerId;
            }

            $subscriptionRow = CompanySubscription::updateOrCreate(
                ['stripe_subscription_id' => $subscriptionId],
                [
                    'company_id' => $company->id,
                    'plan_id' => $plan?->id,
                    'stripe_customer_id' => $stripeCustomerId !== '' ? $stripeCustomerId : $company->stripe_customer_id,
                    'status' => $status,
                    'current_period_start' => is_numeric($periodStart) ? Carbon::createFromTimestamp((int) $periodStart) : null,
                    'current_period_end' => is_numeric($periodEnd) ? Carbon::createFromTimestamp((int) $periodEnd) : null,
                    'cancel_at_period_end' => (bool) data_get($subscription, 'cancel_at_period_end', false),
                    'canceled_at' => is_numeric($cancelAt) ? Carbon::createFromTimestamp((int) $cancelAt) : null,
                    'trial_ends_at' => is_numeric($trialEnd) ? Carbon::createFromTimestamp((int) $trialEnd) : null,
                    'metadata' => $subscription,
                    'last_synced_at' => now(),
                ]
            );

            if (in_array($status, ['active', 'trialing'], true) && $plan) {
                $company->plan = $plan->code;
            } elseif (in_array($status, ['canceled', 'unpaid', 'incomplete_expired'], true)) {
                $company->plan = 'free';
            }

            $company->save();

            return $subscriptionRow;
        });
    }
}
