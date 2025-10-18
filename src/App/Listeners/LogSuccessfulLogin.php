<?php

namespace jeremykenedy\LaravelLogger\App\Listeners;

use Illuminate\Auth\Events\Login;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class LogSuccessfulLogin
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
    public function handle(Login $event): void
    {
        if (config('LaravelLogger.logSuccessfulLogin')) {
            $this->activity(trans('LaravelLogger::laravel-logger.listenerTypes.login'));
        }
    }
}
