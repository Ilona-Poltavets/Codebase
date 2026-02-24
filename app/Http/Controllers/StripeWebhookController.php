<?php

namespace App\Http\Controllers;

use App\Support\SubscriptionSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StripeWebhookController extends Controller
{
    public function __construct(private readonly SubscriptionSyncService $sync)
    {
    }

    public function handle(Request $request): JsonResponse
    {
        if (! (bool) config('services.stripe.billing_enabled', true)) {
            return response()->json(['received' => true, 'ignored' => true]);
        }

        $payload = (string) $request->getContent();
        if (! $this->verifySignature($payload, (string) $request->header('Stripe-Signature', ''))) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        /** @var array<string,mixed> $event */
        $event = json_decode($payload, true) ?: [];
        $type = (string) data_get($event, 'type', '');
        $object = data_get($event, 'data.object');
        if (! is_array($object)) {
            return response()->json(['received' => true]);
        }

        if ($type === 'checkout.session.completed') {
            $this->sync->syncFromCheckoutSession($object);
        }

        if (Str::startsWith($type, 'customer.subscription.')) {
            $this->sync->syncFromStripePayload($object);
        }

        return response()->json(['received' => true]);
    }

    private function verifySignature(string $payload, string $signatureHeader): bool
    {
        $secret = (string) config('services.stripe.webhook_secret');
        if ($secret === '') {
            return false;
        }

        $parts = collect(explode(',', $signatureHeader))
            ->mapWithKeys(function (string $part) {
                [$k, $v] = array_pad(explode('=', trim($part), 2), 2, null);
                return [$k => $v];
            });

        $timestamp = (int) ($parts->get('t') ?? 0);
        $signature = (string) ($parts->get('v1') ?? '');
        if ($timestamp <= 0 || $signature === '') {
            return false;
        }

        if (abs(now()->timestamp - $timestamp) > 300) {
            return false;
        }

        $signedPayload = $timestamp.'.'.$payload;
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expected, $signature);
    }
}
