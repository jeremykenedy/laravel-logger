<?php

namespace jeremykenedy\LaravelLogger;

use Illuminate\Support\ServiceProvider;
use jeremykenedy\LaravelLogger\Facades\Crawler;
use jeremykenedy\LaravelLogger\Support\CrawlerDetect;

class CrawlerDetectServiceProvider extends ServiceProvider
{
    public function register()
    {
        if (! $this->app->bound('LaravelCrawlerDetect')) {
            $this->app->bind('LaravelCrawlerDetect', function ($app) {
                return new CrawlerDetect($app['request']->server());
            });
        }

        $this->app->rebinding('request', function () {
            Crawler::clearResolvedInstance('LaravelCrawlerDetect');
        });
    }
}
