<?php

use App\Models\BillingPlan;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

test('owner can start stripe checkout for active paid plan', function () {
    config()->set('services.stripe.secret', 'sk_test_123');

    [$owner, $company] = createOwnerWithCompany();

    $plan = BillingPlan::where('code', 'pro')->firstOrFail();
    $plan->update([
        'stripe_price_id' => 'price_checkout_123',
        'is_active' => true,
    ]);

    Http::fake([
        'https://api.stripe.com/v1/customers' => Http::response(['id' => 'cus_checkout_123'], 200),
        'https://api.stripe.com/v1/checkout/sessions' => Http::response(['url' => 'https://checkout.stripe.com/c/pay/cs_test_123'], 200),
    ]);

    $this->actingAs($owner)
        ->post(route('billing.checkout', ['plan' => $plan->id]))
        ->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_123');

    expect($company->fresh()->stripe_customer_id)->toBe('cus_checkout_123');
});

test('stripe webhook syncs subscription status and company plan', function () {
    $secret = 'whsec_test_123';
    config()->set('services.stripe.webhook_secret', $secret);

    [$owner, $company] = createOwnerWithCompany();
    $company->update(['stripe_customer_id' => 'cus_webhook_123']);

    $plan = BillingPlan::where('code', 'pro')->firstOrFail();
    $plan->update([
        'stripe_price_id' => 'price_webhook_123',
        'is_active' => true,
    ]);

    $payload = [
        'id' => 'evt_test_123',
        'type' => 'customer.subscription.updated',
        'data' => [
            'object' => [
                'id' => 'sub_webhook_123',
                'customer' => 'cus_webhook_123',
                'status' => 'active',
                'current_period_start' => now()->subDay()->timestamp,
                'current_period_end' => now()->addMonth()->timestamp,
                'cancel_at_period_end' => false,
                'items' => [
                    'data' => [
                        ['price' => ['id' => $plan->stripe_price_id]],
                    ],
                ],
            ],
        ],
    ];

    $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $timestamp = now()->timestamp;
    $signature = hash_hmac('sha256', $timestamp.'.'.$payloadJson, $secret);

    $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$signature,
        ],
        $payloadJson
    )->assertOk();

    $this->assertDatabaseHas('company_subscriptions', [
        'company_id' => $company->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_webhook_123',
        'status' => 'active',
    ]);

    expect($company->fresh()->plan)->toBe($plan->code);
});

test('billing sync command refreshes subscription status from stripe', function () {
    config()->set('services.stripe.secret', 'sk_sync_123');

    [$owner, $company] = createOwnerWithCompany();
    $company->update(['stripe_customer_id' => 'cus_sync_123']);

    $plan = BillingPlan::where('code', 'pro')->firstOrFail();
    $plan->update([
        'stripe_price_id' => 'price_sync_123',
        'is_active' => true,
    ]);

    $subscription = CompanySubscription::create([
        'company_id' => $company->id,
        'plan_id' => $plan->id,
        'stripe_customer_id' => 'cus_sync_123',
        'stripe_subscription_id' => 'sub_sync_123',
        'status' => 'past_due',
        'last_synced_at' => now()->subDay(),
    ]);

    Http::fake([
        'https://api.stripe.com/v1/subscriptions/sub_sync_123' => Http::response([
            'id' => 'sub_sync_123',
            'customer' => 'cus_sync_123',
            'status' => 'active',
            'current_period_start' => now()->subHour()->timestamp,
            'current_period_end' => now()->addMonth()->timestamp,
            'cancel_at_period_end' => false,
            'items' => [
                'data' => [
                    ['price' => ['id' => 'price_sync_123']],
                ],
            ],
        ], 200),
    ]);

    Artisan::call('billing:sync-subscriptions', ['--company' => $company->id]);

    expect($subscription->fresh()->status)->toBe('active');
    expect($company->fresh()->plan)->toBe($plan->code);
});

function createOwnerWithCompany(): array
{
    $owner = User::factory()->create([
        'full_name' => 'Billing Owner',
        'company_id' => null,
    ]);

    $role = Role::firstOrCreate(['name' => 'owner']);
    $owner->roles()->syncWithoutDetaching([$role->id]);

    $company = Company::create([
        'name' => 'Billing Co '.uniqid(),
        'description' => 'Billing company',
        'domain' => 'billing-'.uniqid().'.local',
        'owner_id' => $owner->id,
        'plan' => 'free',
    ]);

    $owner->update(['company_id' => $company->id]);

    return [$owner->fresh(), $company->fresh()];
}
