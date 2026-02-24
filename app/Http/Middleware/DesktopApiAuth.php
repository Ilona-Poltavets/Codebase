<?php

namespace App\Http\Middleware;

use App\Support\DeviceTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DesktopApiAuth
{
    public function __construct(private readonly DeviceTokenService $tokens)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();
        if (! $plainToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = $this->tokens->findActive($plainToken, 'access');
        if (! $token || ! $token->device || ! $token->user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($token->device->revoked_at) {
            return response()->json(['message' => 'Device revoked'], 401);
        }

        $token->update(['last_used_at' => now()]);
        $token->device->update(['last_seen_at' => now()]);

        $request->setUserResolver(fn () => $token->user);
        $request->attributes->set('tracker_token', $token);
        $request->attributes->set('tracker_device', $token->device);

        return $next($request);
    }
}
