<?php

namespace jeremykenedy\LaravelLogger\App\Listeners;

use Illuminate\Auth\Events\Attempting;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class LogAuthenticationAttempt
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
     * Handle the event.
     *
     *
     */
    public function handle(Attempting $event): void
    {
        if (config('LaravelLogger.logAuthAttempts')) {
            $this->activity(trans('LaravelLogger::laravel-logger.listenerTypes.attempt'));
        }
    }
}
