<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripeClient
{
    public function enabled(): bool
    {
        return (string) config('services.stripe.secret') !== '';
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function createCustomer(array $params): array
    {
        return $this->request('post', '/customers', $params);
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function createCheckoutSession(array $params): array
    {
        return $this->request('post', '/checkout/sessions', $params);
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveSubscription(string $subscriptionId): array
    {
        return $this->request('get', '/subscriptions/'.$subscriptionId, []);
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function request(string $method, string $uri, array $params): array
    {
        $secret = (string) config('services.stripe.secret');
        if ($secret === '') {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        $http = Http::asForm()
            ->withBasicAuth($secret, '')
            ->baseUrl('https://api.stripe.com/v1')
            ->acceptJson();

        $response = $method === 'get'
            ? $http->get($uri, $params)
            : $http->post($uri, $params);

        if (! $response->successful()) {
            $message = (string) data_get($response->json(), 'error.message', $response->body());
            throw new RuntimeException('Stripe API error: '.$message);
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return $payload;
    }
}
