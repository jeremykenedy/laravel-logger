<?php

namespace jeremykenedy\LaravelLogger\Facades;

use Illuminate\Support\Facades\Facade;

class Crawler extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'LaravelCrawlerDetect';
    }
}
