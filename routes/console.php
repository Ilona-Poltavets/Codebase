<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Support\UsageReportService;
use App\Models\CompanySubscription;
use Carbon\Carbon;
use App\Support\SubscriptionSyncService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('usage:billing-report {--from=} {--to=} {--company=}', function (UsageReportService $service) {
    $from = $this->option('from') ? Carbon::parse((string) $this->option('from'))->startOfDay() : now()->startOfMonth();
    $to = $this->option('to') ? Carbon::parse((string) $this->option('to'))->endOfDay() : now()->endOfDay();
    $companyId = $this->option('company') ? (int) $this->option('company') : null;

    $rows = $service->billingSummary($from, $to, $companyId);

    if ($rows->isEmpty()) {
        $this->info('No usage events found for selected range.');

        return;
    }

    $this->table(
        ['company_id', 'event_type', 'events_count', 'total_quantity', 'total_billable_units'],
        $rows->all()
    );
})->purpose('Build usage summary prepared for future billing');

Artisan::command('billing:sync-subscriptions {--company=}', function (SubscriptionSyncService $sync) {
    if (! (bool) config('services.stripe.billing_enabled', true)) {
        $this->info('Billing is disabled. Nothing to sync.');

        return;
    }

    $companyId = $this->option('company') ? (int) $this->option('company') : null;

    $query = CompanySubscription::query()->whereNotNull('stripe_subscription_id');
    if ($companyId) {
        $query->where('company_id', $companyId);
    }

    $rows = $query->get();
    if ($rows->isEmpty()) {
        $this->info('No subscriptions to sync.');

        return;
    }

    $synced = 0;
    $failed = 0;

    foreach ($rows as $subscription) {
        try {
            $sync->syncByStripeSubscriptionId((string) $subscription->stripe_subscription_id);
            $synced++;
        } catch (Throwable $e) {
            $failed++;
            $this->error('Failed to sync '.$subscription->stripe_subscription_id.': '.$e->getMessage());
        }
    }

    $this->info("Synced: {$synced}; Failed: {$failed}");
})->purpose('Sync local subscription statuses from Stripe');
