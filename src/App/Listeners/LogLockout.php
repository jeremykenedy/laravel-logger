<?php

namespace jeremykenedy\LaravelLogger\App\Listeners;

use Illuminate\Auth\Events\Lockout;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class LogLockout
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
     */
    public function handle(Lockout $event): void
    {
        if (config('LaravelLogger.logLockOut')) {
            $this->activity(trans('LaravelLogger::laravel-logger.listenerTypes.lockout'));
        }
    }
}
