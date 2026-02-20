<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\ActivityPolicy;
use DeepL\Translator as DeepLClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
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

        $this->app->singleton(
            DeepLClient::class,
            fn () => new DeepLClient(config('services.deepl.api_key'))
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        Gate::policy(Activity::class, ActivityPolicy::class);

        Blade::if('host', function (): bool {
            /** @var User|null $authUser */
            $authUser = Auth::user();

            return (bool) $authUser?->isHost();
        });
    }
}
