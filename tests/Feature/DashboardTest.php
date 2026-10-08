<?php

namespace jeremykenedy\LaravelLogger\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use jeremykenedy\LaravelLogger\App\Http\Controllers\LaravelLoggerController;
use jeremykenedy\LaravelLogger\Tests\DenyAccess;
use jeremykenedy\LaravelLogger\Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_guests_cannot_read_or_change_activity(): void
    {
        foreach (['/activity', '/activity/cleared', '/activity/log/1', '/activity/cleared/log/1', '/activity/export'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->delete('/activity/clear-activity')->assertRedirect('/login');
        $this->delete('/activity/destroy-activity')->assertRedirect('/login');
        $this->post('/activity/restore-log')->assertRedirect('/login');
        $this->post('/activity/live-search')->assertRedirect('/login');
    }

    public function test_legacy_views_remain_the_default(): void
    {
        $this->actingAs($this->createUser());
        $this->createActivity();
        $this->get('/activity')->assertOk()->assertViewIs('LaravelLogger::logger.activity-log');
        $this->assertSame('4', config('LaravelLogger.bootstapVersion'));
        $this->assertNull(config('LaravelLogger.cssFramework'));
    }

    public function test_host_controllers_can_still_pass_a_standard_request(): void
    {
        $this->actingAs($this->createUser());
        $activity = $this->createActivity();
        $controller = new LaravelLoggerController;
        $request = Request::create('/activity');
        $this->assertSame('LaravelLogger::logger.activity-log', $controller->showAccessLog($request)->name());
        $this->assertSame($activity->id, $controller->showAccessLogEntry($request, $activity->id)->getData()['activity']->id);
    }

    public function test_all_view_choices_render_lists_details_and_cleared_entries(): void
    {
        $this->actingAs($this->createUser());
        $activity = $this->createActivity();
        foreach (['bootstrap3', 'bootstrap4', 'bootstrap5', 'tailwind'] as $css) {
            config(['LaravelLogger.cssFramework' => $css, 'LaravelLogger.viewStyle' => 'modern']);
            $this->get('/activity')->assertOk()->assertSee('Viewed dashboard')->assertViewIs('LaravelLogger::modern.activity-log');
            $this->get('/activity/log/'.$activity->id)->assertOk()->assertSee('127.0.0.1');
            $this->get('/activity/cleared')->assertOk();
        }
        $activity->delete();
        $this->get('/activity/cleared/log/'.$activity->id)->assertOk()->assertSee('Viewed dashboard');
        config(['LaravelLogger.viewStyle' => 'legacy', 'LaravelLogger.cssFramework' => 'bootstrap4']);
        $this->get('/activity/cleared/log/'.$activity->id)->assertOk();
    }

    public function test_legacy_bootstrap_three_and_stack_layout_render(): void
    {
        $this->actingAs($this->createUser());
        config(['LaravelLogger.bootstapVersion' => '3', 'LaravelLogger.bladePlacement' => 'stack']);
        $this->get('/activity')->assertOk()->assertSee('panel panel-default');
        config(['LaravelLogger.viewStyle' => 'modern']);
        $this->get('/activity')->assertOk()->assertSee('dashboard.css');
    }

    public function test_detail_pages_return_404_for_missing_entries(): void
    {
        $this->actingAs($this->createUser());
        $this->get('/activity/log/999')->assertNotFound();
        $this->get('/activity/cleared/log/999')->assertNotFound();
    }

    public function test_clear_restore_and_destroy_keep_their_existing_behavior(): void
    {
        $this->actingAs($this->createUser());
        $first = $this->createActivity();
        $second = $this->createActivity();
        $this->delete('/activity/clear-activity')->assertRedirect('/activity');
        $this->assertTrue($first->fresh()->trashed());
        $this->assertTrue($second->fresh()->trashed());
        $this->post('/activity/restore-log')->assertRedirect('/activity');
        $this->assertFalse($first->fresh()->trashed());
        $first->delete();
        $this->delete('/activity/destroy-activity')->assertRedirect('/activity');
        $this->assertNull($first->fresh());
        $this->assertNotNull($second->fresh());
    }

    public function test_log_user_details_use_a_bounded_number_of_queries(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        for ($i = 0; $i < 20; $i++) {
            $this->createActivity(['userId' => $user->id]);
        }
        DB::enableQueryLog();
        $this->get('/activity')->assertOk();
        $queries = array_filter(DB::getQueryLog(), function ($query) {
            return strpos($query['query'], '"users"') !== false;
        });
        $this->assertLessThanOrEqual(3, count($queries));
        DB::disableQueryLog();
    }

    public function test_bulk_changes_keep_model_events_and_do_not_skip_rows(): void
    {
        $this->actingAs($this->createUser());
        $model = config('LaravelLogger.defaultActivityModel');
        $template = $this->createActivity()->getAttributes();
        unset($template['id']);
        $model::insert(array_fill(0, 501, $template));
        $deleted = $restored = 0;
        $model::deleted(function () use (&$deleted) {
            $deleted++;
        });
        $model::restored(function () use (&$restored) {
            $restored++;
        });
        $this->delete('/activity/clear-activity')->assertRedirect('/activity');
        $this->assertSame(502, $deleted);
        $this->assertSame(502, $model::onlyTrashed()->count());
        $this->post('/activity/restore-log')->assertRedirect('/activity');
        $this->assertSame(502, $restored);
        $this->assertSame(502, $model::count());
        $this->delete('/activity/clear-activity')->assertRedirect('/activity');
        $this->delete('/activity/destroy-activity')->assertRedirect('/activity');
        $this->assertSame(1506, $deleted);
        $this->assertSame(0, $model::withTrashed()->count());
    }

    public function test_activity_text_is_escaped_in_each_view(): void
    {
        $this->actingAs($this->createUser());
        $this->createActivity(['description' => '<script>alert(1)</script>', 'route' => 'http://localhost/?q=<script>alert(2)</script>']);
        foreach (['legacy', 'modern'] as $views) {
            config(['LaravelLogger.viewStyle' => $views]);
            $this->get('/activity')->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('<script>alert(2)</script>', false);
        }
    }

    public function test_roles_middleware_still_controls_every_endpoint(): void
    {
        $this->actingAs($this->createUser());
        $this->app['router']->aliasMiddleware('logger-deny', DenyAccess::class);
        config(['LaravelLogger.rolesEnabled' => true, 'LaravelLogger.rolesMiddlware' => 'logger-deny']);
        $this->get('/activity')->assertForbidden();
        $this->get('/activity/export')->assertForbidden();
        $this->delete('/activity/clear-activity')->assertForbidden();
    }
}
