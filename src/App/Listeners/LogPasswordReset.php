<?php

namespace jeremykenedy\LaravelLogger\App\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class LogPasswordReset
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
    public function handle(PasswordReset $event): void
    {
        if (config('LaravelLogger.logPasswordReset')) {
            $this->activity(trans('LaravelLogger::laravel-logger.listenerTypes.reset'));
        }
    }
}
