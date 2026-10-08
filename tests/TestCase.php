<?php

namespace jeremykenedy\LaravelLogger\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Jaybizzle\LaravelCrawlerDetect\LaravelCrawlerDetectServiceProvider;
use jeremykenedy\LaravelLogger\App\Models\Activity;
use jeremykenedy\LaravelLogger\LaravelLoggerServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelLoggerServiceProvider::class, LaravelCrawlerDetectServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $app['config']->set('LaravelLogger.loggerDatabaseConnection', 'sqlite');
        $app['config']->set('LaravelLogger.defaultUserModel', User::class);
        $app['config']->set('LaravelLogger.enableGeoPlugin', false);
        $app['config']->set('LaravelLogger.enableSearch', true);
        $app['config']->set('LaravelLogger.loggerMiddlewareEnabled', false);
        $app['config']->set('LaravelLogger.logSuccessfulLogin', false);
        $app['config']->set('view.paths', [__DIR__.'/fixtures/views']);
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->app['view']->getFinder()->setPaths([__DIR__.'/fixtures/views']);
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true]);
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password')->default('');
            $table->rememberToken();
            $table->timestamps();
        });
        $this->app['router']->get('/login', function () {
            return 'Login';
        })->name('login');
    }

    protected function createUser(array $attributes = [])
    {
        return User::create(array_merge(['name' => 'Test User', 'email' => 'test@example.com'], $attributes));
    }

    protected function createActivity(array $attributes = [])
    {
        $activity = new Activity;
        $activity->forceFill(array_merge([
            'description' => 'Viewed dashboard', 'userType' => 'Registered', 'route' => 'http://localhost/dashboard',
            'ipAddress' => '127.0.0.1', 'methodType' => 'GET', 'userAgent' => 'Mozilla/5.0', 'locale' => 'en-US',
        ], $attributes));
        $activity->save();

        return $activity;
    }
}
