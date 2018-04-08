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
     * @param PasswordReset $event
     *
     * @return void
     */
    public function handle(PasswordReset $event)
    {
        if (config('LaravelLogger.logPasswordReset')) {
            ActivityLogger::activity('Reset Password');
        }
    }
}
