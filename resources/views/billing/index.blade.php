<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __('Billing') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('error'))
                <div class="rounded bg-red-100 text-red-800 px-4 py-3">{{ session('error') }}</div>
            @endif
            @if (session('status') === 'checkout-started')
                <div class="rounded bg-green-100 text-green-800 px-4 py-3">Checkout initiated. Subscription status will sync via webhook.</div>
            @endif
            @if (session('status') === 'checkout-canceled')
                <div class="rounded bg-yellow-100 text-yellow-800 px-4 py-3">Checkout canceled.</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Current Subscription</h3>
                <div class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                    <div>Company: {{ $company->name }}</div>
                    <div>Internal plan: <strong>{{ $company->plan }}</strong></div>
                    <div>Status: <strong>{{ $subscription?->status ?? 'none' }}</strong></div>
                    <div>Stripe subscription: {{ $subscription?->stripe_subscription_id ?? 'n/a' }}</div>
                    <div>Current period end: {{ $subscription?->current_period_end?->format('Y-m-d H:i:s') ?? 'n/a' }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Available Plans</h3>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    @foreach($plans as $plan)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $plan->name }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $plan->description }}</div>
                            <div class="mt-2 text-xl font-bold text-gray-900 dark:text-gray-100">
                                @if($plan->price_cents === 0)
                                    Free
                                @else
                                    ${{ number_format($plan->price_cents / 100, 2) }} / {{ $plan->interval }}
                                @endif
                            </div>

                            <form method="POST" action="{{ route('billing.checkout', $plan->id) }}" class="mt-4">
                                @csrf
                                <button type="submit"
                                        class="w-full px-3 py-2 rounded-md text-sm font-medium text-white {{ $plan->stripe_price_id ? 'bg-indigo-600 hover:bg-indigo-500' : 'bg-gray-400 cursor-not-allowed' }}"
                                        {{ $plan->stripe_price_id ? '' : 'disabled' }}>
                                    {{ $plan->stripe_price_id ? 'Checkout in Stripe' : 'Stripe price not configured' }}
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
