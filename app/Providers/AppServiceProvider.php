<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            StripeClient::class,
            fn () => new StripeClient(config('services.stripe.secret'))
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('host', function (): bool {
            /** @var User $authUser */
            $authUser = Auth::user() ?? new User();

            return $authUser->isHost();
        });
    }
}
