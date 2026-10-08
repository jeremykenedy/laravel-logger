<?php

namespace jeremykenedy\LaravelLogger\Tests\Feature;

use jeremykenedy\LaravelLogger\Tests\TestCase;

class CommandTest extends TestCase
{
    private $environment;

    private $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/logger_commands_'.uniqid();
        mkdir($this->directory);
        $this->environment = $this->directory.'/.env.custom';
        file_put_contents($this->environment, "APP_NAME=Existing\n# LARAVEL_LOGGER_CSS_FRAMEWORK=commented\nOTHER_LARAVEL_LOGGER_CSS_FRAMEWORK=keep\n");
        $this->app->useEnvironmentPath($this->directory);
        $this->app->loadEnvironmentFrom('.env.custom');
    }

    protected function tearDown(): void
    {
        @unlink($this->environment);
        @rmdir($this->directory);
        parent::tearDown();
    }

    public function test_switch_saves_framework_and_theme_without_touching_other_settings(): void
    {
        $this->artisan('logger:switch', ['--css' => 'tailwind', '--theme' => 'dark', '--no-interaction' => true])->assertExitCode(0);
        $contents = file_get_contents($this->environment);
        $this->assertStringContainsString('LARAVEL_LOGGER_CSS_FRAMEWORK=tailwind', $contents);
        $this->assertStringContainsString('LARAVEL_LOGGER_VIEWS=modern', $contents);
        $this->assertStringContainsString('LARAVEL_LOGGER_THEME=dark', $contents);
        $this->assertStringContainsString('APP_NAME=Existing', $contents);
        $this->assertStringContainsString('# LARAVEL_LOGGER_CSS_FRAMEWORK=commented', $contents);
        $this->assertStringContainsString('OTHER_LARAVEL_LOGGER_CSS_FRAMEWORK=keep', $contents);
    }

    public function test_invalid_options_never_partially_write_settings(): void
    {
        $original = file_get_contents($this->environment);
        foreach ([['--css' => 'invalid'], ['--frontend' => 'react'], ['--css' => 'bootstrap5', '--views' => 'legacy'], ['--theme' => 'invalid'], ['--views' => 'invalid']] as $options) {
            $this->artisan('logger:switch', $options + ['--no-interaction' => true])->assertExitCode(1);
            $this->assertSame($original, file_get_contents($this->environment));
        }
    }

    public function test_update_keeps_published_configuration_and_view_overrides(): void
    {
        $config = config_path('laravel-logger.php');
        $view = resource_path('views/vendor/LaravelLogger/logger/activity-log.blade.php');
        if (! is_dir(dirname($view))) {
            mkdir(dirname($view), 0755, true);
        }
        $oldConfig = is_file($config) ? file_get_contents($config) : null;
        $oldView = is_file($view) ? file_get_contents($view) : null;
        file_put_contents($config, "<?php return ['loggerPaginationPerPage' => 17];\n");
        file_put_contents($view, 'Custom activity view');
        try {
            $this->artisan('logger:update', ['--css' => 'bootstrap5', '--publish-views' => true, '--no-interaction' => true])->assertExitCode(0);
            $this->assertSame("<?php return ['loggerPaginationPerPage' => 17];\n", file_get_contents($config));
            $this->assertSame('Custom activity view', file_get_contents($view));
        } finally {
            $oldConfig === null ? unlink($config) : file_put_contents($config, $oldConfig);
            $oldView === null ? unlink($view) : file_put_contents($view, $oldView);
            $this->app['files']->deleteDirectory(resource_path('views/vendor/LaravelLogger'));
        }
    }

    public function test_install_detects_existing_configuration(): void
    {
        $config = config_path('laravel-logger.php');
        file_put_contents($config, '<?php return [];');
        try {
            $this->artisan('logger:install', ['--no-interaction' => true])->assertExitCode(1);
            $this->artisan('logger:install', ['--force' => true, '--no-interaction' => true])->assertExitCode(0);
            $this->assertSame('<?php return [];', file_get_contents($config));
        } finally {
            unlink($config);
        }
    }

    public function test_missing_environment_file_fails_without_creating_one(): void
    {
        unlink($this->environment);
        $this->artisan('logger:switch', ['--css' => 'tailwind', '--no-interaction' => true])->assertExitCode(1);
        $this->assertFileDoesNotExist($this->environment);
    }

    public function test_spaced_exported_and_duplicate_assignments_with_crlf_are_updated(): void
    {
        file_put_contents($this->environment, "APP_NAME=Existing\r\n export LARAVEL_LOGGER_THEME =light\r\nLARAVEL_LOGGER_THEME=system\r\n");
        $this->artisan('logger:switch', ['--theme' => 'dark', '--no-interaction' => true])->assertExitCode(0);
        $contents = file_get_contents($this->environment);
        $this->assertSame(2, substr_count($contents, 'LARAVEL_LOGGER_THEME=dark'));
        $this->assertSame(0, preg_match('/(?<!\r)\n/', $contents));
    }
}
