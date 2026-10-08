<?php

namespace jeremykenedy\LaravelLogger\App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use jeremykenedy\LaravelLogger\App\Http\Requests\ActivityLogRequest;

class ValidateActivityFilters
{
    private $validator;

    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    public function handle(Request $request, Closure $next)
    {
        $this->validator->make($request->query(), (new ActivityLogRequest)->rules())->validate();

        return $next($request);
    }
}
