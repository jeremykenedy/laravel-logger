<?php

namespace jeremykenedy\LaravelLogger\Tests\Feature;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;
use jeremykenedy\LaravelLogger\App\Models\Activity;
use jeremykenedy\LaravelLogger\Tests\TestCase;

class LoggingTest extends TestCase
{
    private function logger()
    {
        return new class
        {
            use ActivityLogger;
        };
    }

    public function test_trait_records_registered_guest_and_related_activity(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $request = Request::create('http://localhost/settings', 'POST', [], [], [], ['REMOTE_ADDR' => '192.0.2.1', 'HTTP_USER_AGENT' => 'Mozilla/5.0', 'HTTP_ACCEPT_LANGUAGE' => 'en-US']);
        $this->app->instance('request', $request);
        $this->logger()->activity('Saved settings', 'Changed timezone', ['id' => 42, 'model' => 'Settings']);
        $activity = Activity::first();
        $this->assertSame('Saved settings', $activity->description);
        $this->assertEquals($user->id, $activity->userId);
        $this->assertEquals(42, $activity->relId);
        $this->assertSame('Settings', $activity->relModel);
        $this->assertSame('192.0.2.1', $activity->ipAddress);
        $this->assertSame('POST', $activity->methodType);
        $this->app['auth']->forgetGuards();
        $this->logger()->activity('Guest visit');
        $this->assertNull(Activity::orderBy('id', 'desc')->first()->userId);
    }

    public function test_logging_uses_laravel_proxy_trust_configuration(): void
    {
        $this->app->instance('request', Request::create('http://localhost/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '192.0.2.1', 'HTTP_X_FORWARDED_FOR' => '198.51.100.1', 'HTTP_CF_CONNECTING_IP' => '203.0.113.1',
        ]));
        $this->logger()->activity('Visited home');
        $this->assertSame('192.0.2.1', Activity::first()->ipAddress);
    }

    public function test_middleware_respects_exclusions_and_disable_setting(): void
    {
        config(['LaravelLogger.loggerMiddlewareEnabled' => true, 'LaravelLogger.loggerMiddlewareExcept' => ['ignored/*']]);
        $this->app['router']->get('/ignored/page', function () {
            return 'Ignored';
        })->middleware('activity');
        $this->app['router']->get('/tracked', function () {
            return 'Tracked';
        })->middleware('activity');
        $this->get('/ignored/page')->assertOk();
        $this->assertSame(0, Activity::count());
        $this->get('/tracked')->assertOk();
        $this->assertSame(1, Activity::count());
        config(['LaravelLogger.loggerMiddlewareEnabled' => false]);
        $this->get('/tracked')->assertOk();
        $this->assertSame(1, Activity::count());
    }

    public function test_failed_validation_logs_an_error_instead_of_inserting_activity(): void
    {
        Log::shouldReceive('error')->once()->with(\Mockery::on(function ($message) {
            return strpos($message, 'Failed Validation') !== false;
        }));
        $this->logger()->activity('Invalid details', ['not' => 'a string']);
        $this->assertSame(0, Activity::count());
    }

    public function test_authentication_listeners_obey_each_configuration_flag(): void
    {
        $user = $this->createUser();
        $events = [
            'logAllAuthEvents' => new Authenticated('web', $user),
            'logAuthAttempts' => new Attempting('web', ['email' => $user->email], false),
            'logFailedAuthAttempts' => new Failed('web', $user, ['email' => $user->email]),
            'logLockOut' => new Lockout(Request::create('/login', 'POST')),
            'logPasswordReset' => new PasswordReset($user),
            'logSuccessfulLogin' => new Login('web', $user, false),
            'logSuccessfulLogout' => new Logout('web', $user),
        ];
        foreach ($events as $flag => $event) {
            config(['LaravelLogger.'.$flag => false]);
            $before = Activity::count();
            Event::dispatch($event);
            $this->assertSame($before, Activity::count());
            config(['LaravelLogger.'.$flag => true]);
            Event::dispatch($event);
            $this->assertSame($before + 1, Activity::count());
            config(['LaravelLogger.'.$flag => false]);
        }
    }

    public function test_null_user_agent_and_locale_are_safe(): void
    {
        $activity = $this->createActivity(['userAgent' => null, 'locale' => null]);
        $this->assertSame('-', $activity->userAgentDetails['browser']);
        $this->actingAs($this->createUser());
        $this->get('/activity')->assertOk();
    }
}
