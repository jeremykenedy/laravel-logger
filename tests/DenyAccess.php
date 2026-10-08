<?php

namespace jeremykenedy\LaravelLogger\Tests;

class DenyAccess
{
    public function handle($request, $next)
    {
        abort(403);
    }
}
