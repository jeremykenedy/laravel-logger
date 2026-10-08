<?php

namespace jeremykenedy\LaravelLogger\Facades;

use Illuminate\Support\Facades\Facade;
use jeremykenedy\LaravelLogger\Support\CrawlerDetect;

/**
 * @method static bool isCrawler(?string $userAgent = null)
 *
 * @mixin CrawlerDetect
 */
class Crawler extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'LaravelCrawlerDetect';
    }
}
