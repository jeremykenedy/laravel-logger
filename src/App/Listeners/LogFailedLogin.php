<?php

namespace jeremykenedy\LaravelLogger\App\Listeners;

use Illuminate\Auth\Events\Failed;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class LogFailedLogin
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
    public function handle(Failed $event): void
    {
        if (config('LaravelLogger.logFailedAuthAttempts')) {
            $this->activity(trans('LaravelLogger::laravel-logger.listenerTypes.failed'));
        }
    }
}
