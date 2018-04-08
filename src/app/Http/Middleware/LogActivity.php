<?php

namespace jeremykenedy\LaravelLogger\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class LogActivity
{
    use ActivityLogger;

    /**
     * Handle an incoming request.
     *
     * @param Request  $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $description = null)
    {
        if (config('LaravelLogger.loggerMiddlewareEnabled')) {
            ActivityLogger::activity($description);
        }

        return $next($request);
    }
}
