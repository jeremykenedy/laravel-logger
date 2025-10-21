<?php

namespace jeremykenedy\LaravelLogger;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use jeremykenedy\LaravelLogger\App\Http\Middleware\LogActivity;

class LaravelLoggerServiceProvider extends ServiceProvider
{
    const DISABLE_DEFAULT_ROUTES_CONFIG = 'laravel-logger.disableRoutes';

    /**
     * The event listener mappings for the applications auth scafolding.
     *
     * @var array
     */
    protected $listeners = [

        'Illuminate\Auth\Events\Attempting' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogAuthenticationAttempt',
        ],

        'Illuminate\Auth\Events\Authenticated' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogAuthenticated',
        ],

        'Illuminate\Auth\Events\Login' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogSuccessfulLogin',
        ],

        'Illuminate\Auth\Events\Failed' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogFailedLogin',
        ],

        'Illuminate\Auth\Events\Logout' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogSuccessfulLogout',
        ],

        'Illuminate\Auth\Events\Lockout' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogLockout',
        ],

        'Illuminate\Auth\Events\PasswordReset' => [
            'jeremykenedy\LaravelLogger\App\Listeners\LogPasswordReset',
        ],

    ];

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app['router']->middlewareGroup('activity', [LogActivity::class]);

        // Load translations from new Laravel 9+ structure if available, fallback to old structure
        if (is_dir(__DIR__.'/lang/')) {
            $this->loadTranslationsFrom(__DIR__.'/lang/', 'LaravelLogger');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/resources/lang/', 'LaravelLogger');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        if (file_exists(config_path('laravel-logger.php'))) {
            $this->mergeConfigFrom(config_path('laravel-logger.php'), 'LaravelLogger');
        } else {
            $this->mergeConfigFrom(__DIR__.'/config/laravel-logger.php', 'LaravelLogger');
        }

        if (config(self::DISABLE_DEFAULT_ROUTES_CONFIG) == false) {
            $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        }

        $this->loadViewsFrom(__DIR__.'/resources/views/', 'LaravelLogger');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->registerEventListeners();
        $this->publishFiles();
    }

    /**
     * Get the list of listeners and events.
     *
     * @return array
     */
    private function getListeners()
    {
        return $this->listeners;
    }

    /**
     * Register the list of listeners and events.
     */
    private function registerEventListeners(): void
    {
        $listeners = $this->getListeners();
        foreach ($listeners as $event => $eventListeners) {
            foreach ($eventListeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * Publish files for Laravel Logger.
     */
    private function publishFiles(): void
    {
        $publishTag = 'LaravelLogger';

        $this->publishes([
            __DIR__.'/config/laravel-logger.php' => base_path('config/laravel-logger.php'),
        ], $publishTag);

        $this->publishes([
            __DIR__.'/resources/views' => base_path('resources/views/vendor/'.$publishTag),
        ], $publishTag);

        // Publish language files to Laravel 9+ structure if available, fallback to old structure
        if (is_dir(__DIR__.'/lang/')) {
            // Laravel 9+ structure
            $this->publishes([
                __DIR__.'/lang' => base_path('lang/vendor/'.$publishTag),
            ], $publishTag);

            // Also publish to old structure for backward compatibility
            $this->publishes([
                __DIR__.'/lang' => base_path('resources/lang/vendor/'.$publishTag),
            ], $publishTag.'-legacy');
        } else {
            // Old structure fallback
            $this->publishes([
                __DIR__.'/resources/lang' => base_path('resources/lang/vendor/'.$publishTag),
            ], $publishTag);
        }
    }
}
