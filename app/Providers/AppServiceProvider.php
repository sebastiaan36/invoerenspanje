<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use App\Services\Blog\BlogFileSync;
use App\Services\Bpm\BpmCalculator;
use App\Services\Hacienda\HaciendaPriceImporter;
use App\Services\Hacienda\HaciendaVehicleMatcher;
use App\Services\Rdw\RdwService;
use App\Services\SpainImport\SpainImportCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Role-based redirect na login (admin → /admin, klant → /portaal).
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        // Blog posts are authored as Markdown files and synced with `blog:sync`.
        $this->app->bind(BlogFileSync::class, fn () => new BlogFileSync(resource_path('content/blog')));

        $this->app->singleton(BpmCalculator::class, fn ($app): BpmCalculator => new BpmCalculator(
            $app['config']->get('bpm_rates'),
        ));

        // Hacienda "precios medios de venta" are committed as CSV and loaded with `hacienda:sync`.
        $this->app->bind(HaciendaPriceImporter::class, fn ($app): HaciendaPriceImporter => new HaciendaPriceImporter(
            $app->make(CacheRepository::class),
            database_path('data/hacienda'),
        ));

        $this->app->bind(HaciendaVehicleMatcher::class, fn ($app): HaciendaVehicleMatcher => new HaciendaVehicleMatcher(
            tableYear: (int) $app['config']->get('spain_import.hacienda.table_year'),
            cache: $app->make(CacheRepository::class),
            logger: $app->make(LogManager::class)->channel(),
            cacheTtlSeconds: (int) $app['config']->get('spain_import.hacienda.match_cache_ttl_seconds', 86_400),
        ));

        $this->app->singleton(SpainImportCalculator::class, fn ($app): SpainImportCalculator => new SpainImportCalculator(
            $app['config']->get('spain_import'),
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
                notFoundCacheTtlMinutes: (int) ($config['not_found_cache_ttl_minutes'] ?? 60),
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
        $this->configureRateLimiting();
    }

    /**
     * Named limiters for the public routes. Each limiter keeps its own counter
     * (prefixed with its name), unlike `throttle:X,1`, which shares one per-IP
     * counter across every route that uses it.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('contact', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('kenteken-lookup', fn (Request $request): Limit => Limit::perMinute(20)->by($request->ip()));
        RateLimiter::for('lead-submit', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('feedback-view', fn (Request $request): Limit => Limit::perMinute(30)->by($request->ip().'|'.$request->route('token')));
        RateLimiter::for('feedback-submit', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip().'|'.$request->route('token')));
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
