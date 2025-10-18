<?php

namespace jeremykenedy\LaravelLogger\App\Listeners;

use Illuminate\Auth\Events\Logout;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class LogSuccessfulLogout
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
    public function handle(Logout $event): void
    {
        if (config('LaravelLogger.logSuccessfulLogout')) {
            $this->activity(trans('LaravelLogger::laravel-logger.listenerTypes.logout'));
        }
    }
}
