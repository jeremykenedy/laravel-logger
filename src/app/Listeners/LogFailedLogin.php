<?php

namespace jeremykenedy\LaravelLogger\App\Listeners;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Failed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
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
     * @param  Failed  $event
     * @return void
     */
    public function handle(Failed $event)
    {
        if (config('LaravelLogger.logFailedAuthAttempts')) {
            ActivityLogger::activity("Failed Login Attempt");
        }
    }
}
