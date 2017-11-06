<?php

namespace jeremykenedy\LaravelLogger\App\Listeners;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Login;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
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
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        if (config('LaravelLogger.logSuccessfulLogin')) {
            ActivityLogger::activity("Logged In");
        }
    }
}
