<?php

namespace jeremykenedy\LaravelLogger\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use jeremykenedy\LaravelLogger\App\Models\Activity;
use jeremykenedy\LaravelLogger\Tests\TestCase;

class FilteringTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-10-07 12:00:00'));
        $this->actingAs($this->createUser());
        config(['LaravelLogger.loggerPaginationEnabled' => false]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_date_range_is_inclusive(): void
    {
        $first = $this->createActivity(['created_at' => '2026-10-05 00:00:00']);
        $last = $this->createActivity(['created_at' => '2026-10-06 23:59:59']);
        $this->createActivity(['created_at' => '2026-10-07 00:00:00']);
        $response = $this->get('/activity?date_from=2026-10-05&date_to=2026-10-06')->assertOk();
        $this->assertSame([$last->id, $first->id], $response->viewData('activities')->pluck('id')->all());
    }

    public function test_every_predefined_period_filters_records(): void
    {
        foreach (['today' => now()->startOfDay(), 'yesterday' => now()->subDay()->startOfDay(), 'last_7_days' => now()->subDays(7), 'last_30_days' => now()->subDays(30), 'last_3_months' => now()->subMonths(3), 'last_6_months' => now()->subMonths(6), 'last_year' => now()->subYear()] as $period => $boundary) {
            Activity::withTrashed()->forceDelete();
            $inside = $this->createActivity(['created_at' => $boundary->copy()->addMinute()]);
            $this->createActivity(['created_at' => $boundary->copy()->subMinute()]);
            $response = $this->get('/activity?period='.$period)->assertOk();
            $this->assertSame([$inside->id], $response->viewData('activities')->pluck('id')->all());
        }
    }

    public function test_date_filters_can_be_disabled(): void
    {
        config(['LaravelLogger.enableDateFiltering' => false]);
        $this->createActivity(['created_at' => now()->subYear()]);
        $this->assertCount(1, $this->get('/activity?period=today')->assertOk()->viewData('activities'));
    }

    public function test_invalid_dates_and_array_filters_return_validation_errors(): void
    {
        $this->getJson('/activity?date_from=not-a-date')->assertStatus(422);
        $this->getJson('/activity?description[]=bad')->assertStatus(422);
        $this->getJson('/activity/export?format[]=csv')->assertStatus(422);
    }

    public function test_unknown_periods_keep_the_existing_unfiltered_behavior(): void
    {
        $this->createActivity();
        $this->assertCount(1, $this->get('/activity?period=unknown')->assertOk()->viewData('activities'));
    }

    public function test_all_search_fields_are_applied_together_and_preserved_in_pagination(): void
    {
        $user = $this->createUser(['email' => 'other@example.com']);
        $match = $this->createActivity(['description' => 'Saved settings', 'userId' => $user->id, 'methodType' => 'POST', 'route' => 'http://localhost/settings', 'ipAddress' => '192.0.2.1']);
        $this->createActivity();
        config(['LaravelLogger.loggerPaginationEnabled' => true, 'LaravelLogger.loggerPaginationPerPage' => 1]);
        $query = http_build_query(['description' => 'Saved', 'user' => $user->id, 'method' => 'POST', 'route' => 'settings', 'ip_address' => '192.0.2']);
        $response = $this->get('/activity?'.$query)->assertOk();
        $this->assertSame([$match->id], $response->viewData('activities')->pluck('id')->all());
        $this->assertStringContainsString('description=Saved', $response->viewData('activities')->url(1));
    }

    public function test_cleared_and_related_activity_lists_obey_date_filters(): void
    {
        $old = $this->createActivity(['created_at' => now()->subWeek()]);
        $today = $this->createActivity();
        $response = $this->get('/activity/log/'.$today->id.'?period=today')->assertOk();
        $this->assertSame([$today->id], $response->viewData('userActivities')->pluck('id')->all());
        $old->delete();
        $today->delete();
        $this->assertSame([$today->id], $this->get('/activity/cleared?period=today')->assertOk()->viewData('activities')->pluck('id')->all());
    }

    public function test_cursor_pagination_filters_dates_and_handles_tied_timestamps(): void
    {
        if (! method_exists(Builder::class, 'cursorPaginate')) {
            $this->markTestSkipped('Cursor pagination requires Laravel 8.');
        }
        config(['LaravelLogger.loggerCursorPaginationEnabled' => true, 'LaravelLogger.loggerPaginationPerPage' => 1, 'LaravelLogger.viewStyle' => 'modern']);
        $this->createActivity(['created_at' => now()->subWeek()]);
        $first = $this->createActivity();
        $second = $this->createActivity();
        $page = $this->get('/activity?period=today')->assertOk()->viewData('activities');
        $this->assertSame([$second->id], $page->pluck('id')->all());
        $next = $this->get($page->nextPageUrl())->assertOk()->viewData('activities');
        $this->assertSame([$first->id], $next->pluck('id')->all());
        $this->assertNull($next->nextPageUrl());
        $first->delete();
        $second->delete();
        $this->assertSame([$second->id], $this->get('/activity/cleared?period=today')->assertOk()->viewData('activities')->pluck('id')->all());
    }
}
