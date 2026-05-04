<?php

namespace App\Providers;

use App\Services\Bpm\BpmCalculator;
use App\Services\Rdw\RdwService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BpmCalculator::class, fn ($app): BpmCalculator => new BpmCalculator(
            $app['config']->get('bpm'),
        ));

        $this->app->singleton(RdwService::class, function ($app): RdwService {
            $config = $app['config']->get('services.rdw');

            return new RdwService(
                http: $app->make(HttpFactory::class),
                cache: $app->make(CacheRepository::class),
                logger: $app->make(LogManager::class)->channel('rdw'),
                vehicleEndpoint: $config['vehicle_endpoint'],
                fuelEndpoint: $config['fuel_endpoint'],
                appToken: $config['app_token'] ?? null,
                cacheTtlDays: (int) $config['cache_ttl_days'],
                timeoutSeconds: (int) $config['timeout_seconds'],
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
