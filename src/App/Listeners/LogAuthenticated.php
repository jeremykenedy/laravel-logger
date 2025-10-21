<?php

namespace jeremykenedy\LaravelLogger\App\Listeners;

use Illuminate\Auth\Events\Authenticated;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class LogAuthenticated
{
    use ActivityLogger;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle ANY authenticated event.
     */
    public function handle(Authenticated $event): void
    {
        if (config('LaravelLogger.logAllAuthEvents')) {
            $this->activity(trans('LaravelLogger::laravel-logger.listenerTypes.auth'));
        }
    }
}
