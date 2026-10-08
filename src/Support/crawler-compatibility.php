<?php

use jeremykenedy\LaravelLogger\CrawlerDetectServiceProvider;
use jeremykenedy\LaravelLogger\Facades\Crawler;
use jeremykenedy\LaravelLogger\Support\CrawlerDetect;

spl_autoload_register(static function ($class): void {
    $aliases = [
        'jaybizzle\crawlerdetect\crawlerdetect' => CrawlerDetect::class,
        'jaybizzle\laravelcrawlerdetect\facades\laravelcrawlerdetect' => Crawler::class,
        'jaybizzle\laravelcrawlerdetect\laravelcrawlerdetectserviceprovider' => CrawlerDetectServiceProvider::class,
    ];

    $name = strtolower($class);
    if (isset($aliases[$name])) {
        class_alias($aliases[$name], $class);
    }
});
