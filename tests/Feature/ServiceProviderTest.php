<?php

namespace jeremykenedy\LaravelLogger\Tests\Feature;

use jeremykenedy\LaravelLogger\LaravelLoggerServiceProvider;
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

    public function test_older_published_configuration_keeps_its_settings_and_gets_missing_defaults(): void
    {
        $path = config_path('laravel-logger.php');
        $original = is_file($path) ? file_get_contents($path) : null;
        file_put_contents($path, "<?php return ['bootstapVersion' => '3', 'loggerPaginationPerPage' => 17];");
        try {
            config(['LaravelLogger' => []]);
            (new LaravelLoggerServiceProvider($this->app))->register();
            $this->assertSame('3', config('LaravelLogger.bootstapVersion'));
            $this->assertSame(17, config('LaravelLogger.loggerPaginationPerPage'));
            $this->assertSame('legacy', config('LaravelLogger.viewStyle'));
            $this->assertNull(config('LaravelLogger.cssFramework'));
            $this->assertSame('system', config('LaravelLogger.theme'));
        } finally {
            $original === null ? unlink($path) : file_put_contents($path, $original);
        }
    }
}
