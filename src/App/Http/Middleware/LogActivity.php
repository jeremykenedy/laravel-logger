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
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $description = null)
    {
        if (config('LaravelLogger.loggerMiddlewareEnabled') && $this->shouldLog($request)) {
            $this->activity($description);
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should log.
     */
    protected function shouldLog(Request $request): bool
    {
        foreach (config('LaravelLogger.loggerMiddlewareExcept', []) as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return false;
            }
        }

        return true;
    }
}
