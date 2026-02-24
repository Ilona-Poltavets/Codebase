<?php

namespace App\Http\Controllers;

use App\Models\BillingPlan;
use App\Models\Company;
use App\Support\StripeClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class BillingController extends Controller
{
    public function __construct(private readonly StripeClient $stripe)
    {
    }

    public function index(Request $request): View
    {
        if (! $this->billingEnabled()) {
            abort(503, 'Billing is temporarily disabled.');
        }

        $company = $this->resolveCompany($request);
        $plans = BillingPlan::where('is_active', true)->orderBy('price_cents')->get();
        $subscription = $company->subscriptions()->with('plan')->latest('id')->first();

        return view('billing.index', compact('company', 'plans', 'subscription'));
    }

    public function checkout(Request $request, BillingPlan $plan): RedirectResponse
    {
        if (! $this->billingEnabled()) {
            return back()->with('error', 'Billing is temporarily disabled.');
        }

        $company = $this->resolveCompany($request);

        if (! $plan->is_active || ! $plan->stripe_price_id) {
            return back()->with('error', 'Selected plan is not available for Stripe checkout.');
        }

        $ownerEmail = $company->owner?->email ?: $request->user()->email;

        try {
            if (! $company->stripe_customer_id) {
                $customer = $this->stripe->createCustomer([
                    'name' => $company->name,
                    'email' => $ownerEmail,
                    'metadata[company_id]' => (string) $company->id,
                ]);

                $company->stripe_customer_id = (string) ($customer['id'] ?? null);
                $company->save();
            }

            $checkout = $this->stripe->createCheckoutSession([
                'mode' => 'subscription',
                'customer' => (string) $company->stripe_customer_id,
                'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.cancel'),
                'line_items[0][price]' => $plan->stripe_price_id,
                'line_items[0][quantity]' => '1',
                'client_reference_id' => (string) $company->id,
                'metadata[company_id]' => (string) $company->id,
                'metadata[plan_id]' => (string) $plan->id,
            ]);

            $checkoutUrl = (string) ($checkout['url'] ?? '');
            if ($checkoutUrl === '') {
                return back()->with('error', 'Stripe checkout session did not return a redirect URL.');
            }

            return redirect()->away($checkoutUrl);
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Unable to create Stripe checkout session: '.$e->getMessage());
        }
    }

    public function success(): RedirectResponse
    {
        if (! $this->billingEnabled()) {
            return redirect()->route('profile.edit')->with('error', 'Billing is temporarily disabled.');
        }

        return redirect()->route('billing.index')->with('status', 'checkout-started');
    }

    public function cancel(): RedirectResponse
    {
        if (! $this->billingEnabled()) {
            return redirect()->route('profile.edit')->with('error', 'Billing is temporarily disabled.');
        }

        return redirect()->route('billing.index')->with('status', 'checkout-canceled');
    }

    private function billingEnabled(): bool
    {
        return (bool) config('services.stripe.billing_enabled', true);
    }

    private function resolveCompany(Request $request): Company
    {
        $user = $request->user();
        if (! $user || ! $user->company_id) {
            abort(403);
        }

        if (! $user->hasRole('owner') && ! $user->hasRole('admin')) {
            abort(403);
        }

        return Company::findOrFail($user->company_id);
    }
}
