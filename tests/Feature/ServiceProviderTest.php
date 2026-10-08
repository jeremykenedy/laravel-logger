<?php

namespace jeremykenedy\LaravelLogger\Tests\Feature;

use jeremykenedy\LaravelLogger\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_view_paths_have_no_trailing_slash(): void
    {
        $paths = $this->app['view']->getFinder()->getHints()['LaravelLogger'];
        $this->assertContains(dirname(__DIR__, 2).'/src/resources/views', $paths);
        foreach ($paths as $path) {
            $this->assertSame(rtrim($path, '/\\'), $path);
        }
        $this->assertTrue($this->app['view']->exists('LaravelLogger::logger.activity-log'));
    }

    public function test_package_translations_are_loaded(): void
    {
        $this->assertSame('Activity Log', trans('LaravelLogger::laravel-logger.dashboard.title'));
    }
}
