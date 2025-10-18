<?php

namespace jeremykenedy\LaravelLogger\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use jeremykenedy\LaravelLogger\App\Http\Controllers\LaravelLoggerController;
use jeremykenedy\LaravelLogger\App\Models\Activity;
use jeremykenedy\LaravelLogger\Tests\TestCase;

class DateFilteringTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new LaravelLoggerController();
        
        Config::set('LaravelLogger.defaultActivityModel', Activity::class);
        Config::set('LaravelLogger.enableDateFiltering', true);
    }

    /** @test */
    public function it_filters_activities_by_exact_date()
    {
        $specificDate = Carbon::parse('2024-01-15');
        
        $activity1 = Activity::factory()->create([
            'created_at' => $specificDate,
            'description' => 'Activity on specific date'
        ]);

        $activity2 = Activity::factory()->create([
            'created_at' => $specificDate->addDay(),
            'description' => 'Activity on next day'
        ]);

        $request = new Request([
            'date_from' => '2024-01-15',
            'date_to' => '2024-01-15'
        ]);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertEquals($activity1->id, $results->first()->id);
    }

    /** @test */
    public function it_filters_activities_by_date_range()
    {
        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-01-31');

        $activity1 = Activity::factory()->create([
            'created_at' => $startDate->addDays(5),
            'description' => 'Activity in range'
        ]);

        $activity2 = Activity::factory()->create([
            'created_at' => $startDate->addDays(10),
            'description' => 'Another activity in range'
        ]);

        $activity3 = Activity::factory()->create([
            'created_at' => $endDate->addDays(5),
            'description' => 'Activity outside range'
        ]);

        $request = new Request([
            'date_from' => '2024-01-01',
            'date_to' => '2024-01-31'
        ]);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $activity1->id));
        $this->assertTrue($results->contains('id', $activity2->id));
        $this->assertFalse($results->contains('id', $activity3->id));
    }

    /** @test */
    public function it_filters_activities_by_today_period()
    {
        $today = Activity::factory()->today()->create([
            'description' => 'Today activity'
        ]);

        $yesterday = Activity::factory()->yesterday()->create([
            'description' => 'Yesterday activity'
        ]);

        $request = new Request(['period' => 'today']);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertEquals($today->id, $results->first()->id);
    }

    /** @test */
    public function it_filters_activities_by_yesterday_period()
    {
        $today = Activity::factory()->today()->create([
            'description' => 'Today activity'
        ]);

        $yesterday = Activity::factory()->yesterday()->create([
            'description' => 'Yesterday activity'
        ]);

        $request = new Request(['period' => 'yesterday']);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertEquals($yesterday->id, $results->first()->id);
    }

    /** @test */
    public function it_filters_activities_by_last_7_days_period()
    {
        $today = Activity::factory()->today()->create();
        $threeDaysAgo = Activity::factory()->create([
            'created_at' => now()->subDays(3)
        ]);
        $tenDaysAgo = Activity::factory()->create([
            'created_at' => now()->subDays(10)
        ]);

        $request = new Request(['period' => 'last_7_days']);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $today->id));
        $this->assertTrue($results->contains('id', $threeDaysAgo->id));
        $this->assertFalse($results->contains('id', $tenDaysAgo->id));
    }

    /** @test */
    public function it_filters_activities_by_last_30_days_period()
    {
        $recent = Activity::factory()->create([
            'created_at' => now()->subDays(15)
        ]);

        $old = Activity::factory()->create([
            'created_at' => now()->subDays(45)
        ]);

        $request = new Request(['period' => 'last_30_days']);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertEquals($recent->id, $results->first()->id);
    }

    /** @test */
    public function it_filters_activities_by_last_3_months_period()
    {
        $recent = Activity::factory()->create([
            'created_at' => now()->subMonths(1)
        ]);

        $old = Activity::factory()->create([
            'created_at' => now()->subMonths(4)
        ]);

        $request = new Request(['period' => 'last_3_months']);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertEquals($recent->id, $results->first()->id);
    }

    /** @test */
    public function it_filters_activities_by_last_6_months_period()
    {
        $recent = Activity::factory()->create([
            'created_at' => now()->subMonths(3)
        ]);

        $old = Activity::factory()->create([
            'created_at' => now()->subMonths(8)
        ]);

        $request = new Request(['period' => 'last_6_months']);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertEquals($recent->id, $results->first()->id);
    }

    /** @test */
    public function it_filters_activities_by_last_year_period()
    {
        $recent = Activity::factory()->create([
            'created_at' => now()->subMonths(6)
        ]);

        $old = Activity::factory()->create([
            'created_at' => now()->subYear()->subMonth()
        ]);

        $request = new Request(['period' => 'last_year']);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertEquals($recent->id, $results->first()->id);
    }

    /** @test */
    public function it_handles_invalid_period_gracefully()
    {
        $activity = Activity::factory()->create();

        $request = new Request(['period' => 'invalid_period']);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        // Should return all activities when period is invalid
        $this->assertCount(1, $results);
        $this->assertEquals($activity->id, $results->first()->id);
    }

    /** @test */
    public function it_handles_empty_request_gracefully()
    {
        $activity = Activity::factory()->create();

        $request = new Request([]);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        // Should return all activities when no filters are applied
        $this->assertCount(1, $results);
        $this->assertEquals($activity->id, $results->first()->id);
    }

    /** @test */
    public function it_combines_date_range_and_period_filters()
    {
        $activity1 = Activity::factory()->create([
            'created_at' => now()->subDays(5)
        ]);

        $activity2 = Activity::factory()->create([
            'created_at' => now()->subDays(15)
        ]);

        $activity3 = Activity::factory()->create([
            'created_at' => now()->subDays(25)
        ]);

        $request = new Request([
            'date_from' => now()->subDays(10)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
            'period' => 'last_30_days'
        ]);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        // Should apply both filters (intersection)
        $this->assertCount(1, $results);
        $this->assertEquals($activity1->id, $results->first()->id);
    }

    /** @test */
    public function it_preserves_query_order()
    {
        $activity1 = Activity::factory()->create([
            'created_at' => now()->subDays(1),
            'description' => 'First activity'
        ]);

        $activity2 = Activity::factory()->create([
            'created_at' => now()->subDays(2),
            'description' => 'Second activity'
        ]);

        $request = new Request(['period' => 'last_7_days']);

        $query = Activity::query()->orderBy('created_at', 'desc');
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        $this->assertCount(2, $results);
        $this->assertEquals($activity1->id, $results->first()->id);
        $this->assertEquals($activity2->id, $results->last()->id);
    }

    /** @test */
    public function it_handles_timezone_correctly()
    {
        // Set a specific timezone for testing
        $originalTimezone = config('app.timezone');
        config(['app.timezone' => 'UTC']);

        $activity = Activity::factory()->create([
            'created_at' => Carbon::parse('2024-01-15 12:00:00', 'UTC')
        ]);

        $request = new Request([
            'date_from' => '2024-01-15',
            'date_to' => '2024-01-15'
        ]);

        $query = Activity::query();
        $filteredQuery = $this->controller->applyDateFilter($query, $request);
        $results = $filteredQuery->get();

        $this->assertCount(1, $results);
        $this->assertEquals($activity->id, $results->first()->id);

        // Restore original timezone
        config(['app.timezone' => $originalTimezone]);
    }
}
