<?php

namespace App\Providers;

use App\Support\SecurityAuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Event::listen(Login::class, function (Login $event): void {
            SecurityAuditLogger::log(
                eventType: 'auth.login.success',
                userId: $event->user->id,
                companyId: $event->user->company_id,
                context: ['guard' => $event->guard]
            );
        });

        Event::listen(Failed::class, function (Failed $event): void {
            SecurityAuditLogger::log(
                eventType: 'auth.login.failed',
                userId: $event->user?->id,
                companyId: $event->user?->company_id,
                context: [
                    'guard' => $event->guard,
                    'email' => isset($event->credentials['email']) ? mb_strtolower((string) $event->credentials['email']) : null,
                ]
            );
        });

        Event::listen(Lockout::class, function (Lockout $event): void {
            SecurityAuditLogger::log(
                eventType: 'auth.login.lockout',
                context: ['email' => mb_strtolower((string) $event->request->input('email', ''))],
                request: $event->request
            );
        });

        Event::listen(Logout::class, function (Logout $event): void {
            SecurityAuditLogger::log(
                eventType: 'auth.logout',
                userId: $event->user?->id,
                companyId: $event->user?->company_id,
                context: ['guard' => $event->guard]
            );
        });

        Event::listen(Registered::class, function (Registered $event): void {
            SecurityAuditLogger::log(
                eventType: 'auth.registered',
                userId: $event->user->id,
                companyId: $event->user->company_id
            );
        });

        Event::listen(Verified::class, function (Verified $event): void {
            SecurityAuditLogger::log(
                eventType: 'auth.email.verified',
                userId: $event->user->id,
                companyId: $event->user->company_id
            );
        });

        Event::listen(PasswordReset::class, function (PasswordReset $event): void {
            SecurityAuditLogger::log(
                eventType: 'auth.password.reset',
                userId: $event->user->id,
                companyId: $event->user->company_id
            );
        });
    }
}
