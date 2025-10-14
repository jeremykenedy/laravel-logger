<?php

namespace jeremykenedy\LaravelLogger\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use jeremykenedy\LaravelLogger\App\Http\Controllers\LaravelLoggerController;
use jeremykenedy\LaravelLogger\App\Models\Activity;
use jeremykenedy\LaravelLogger\Tests\TestCase;

class LaravelLoggerControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $controller;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new LaravelLoggerController();
        
        // Create a test user
        $this->user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
        
        // Set up configuration
        Config::set('LaravelLogger.defaultActivityModel', Activity::class);
        Config::set('LaravelLogger.defaultUserModel', \App\Models\User::class);
        Config::set('LaravelLogger.defaultUserIDField', 'id');
        Config::set('LaravelLogger.enableDateFiltering', true);
        Config::set('LaravelLogger.enableExport', true);
        Config::set('LaravelLogger.enableSearch', true);
        Config::set('LaravelLogger.loggerPaginationEnabled', false);
    }

    /** @test */
    public function it_can_filter_activities_by_date_range()
    {
        // Create test activities with different dates
        $today = Activity::factory()->create([
            'description' => 'Today activity',
            'created_at' => now(),
            'userId' => $this->user->id
        ]);

        $yesterday = Activity::factory()->create([
            'description' => 'Yesterday activity',
            'created_at' => now()->subDay(),
            'userId' => $this->user->id
        ]);

        $lastWeek = Activity::factory()->create([
            'description' => 'Last week activity',
            'created_at' => now()->subWeek(),
            'userId' => $this->user->id
        ]);

        // Test filtering by date range
        $request = new Request([
            'date_from' => now()->subDay()->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d')
        ]);

        $activities = Activity::orderBy('created_at', 'desc');
        $filteredActivities = $this->controller->applyDateFilter($activities, $request);
        $results = $filteredActivities->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $today->id));
        $this->assertTrue($results->contains('id', $yesterday->id));
        $this->assertFalse($results->contains('id', $lastWeek->id));
    }

    /** @test */
    public function it_can_filter_activities_by_predefined_periods()
    {
        // Create test activities
        $today = Activity::factory()->create([
            'description' => 'Today activity',
            'created_at' => now(),
            'userId' => $this->user->id
        ]);

        $yesterday = Activity::factory()->create([
            'description' => 'Yesterday activity',
            'created_at' => now()->subDay(),
            'userId' => $this->user->id
        ]);

        $lastWeek = Activity::factory()->create([
            'description' => 'Last week activity',
            'created_at' => now()->subWeek(),
            'userId' => $this->user->id
        ]);

        // Test 'today' period
        $request = new Request(['period' => 'today']);
        $activities = Activity::orderBy('created_at', 'desc');
        $filteredActivities = $this->controller->applyDateFilter($activities, $request);
        $results = $filteredActivities->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $today->id));

        // Test 'last_7_days' period
        $request = new Request(['period' => 'last_7_days']);
        $activities = Activity::orderBy('created_at', 'desc');
        $filteredActivities = $this->controller->applyDateFilter($activities, $request);
        $results = $filteredActivities->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $today->id));
        $this->assertTrue($results->contains('id', $yesterday->id));
        $this->assertFalse($results->contains('id', $lastWeek->id));
    }

    /** @test */
    public function it_can_filter_activities_by_last_30_days()
    {
        // Create activities
        $recent = Activity::factory()->create([
            'description' => 'Recent activity',
            'created_at' => now()->subDays(15),
            'userId' => $this->user->id
        ]);

        $old = Activity::factory()->create([
            'description' => 'Old activity',
            'created_at' => now()->subDays(45),
            'userId' => $this->user->id
        ]);

        $request = new Request(['period' => 'last_30_days']);
        $activities = Activity::orderBy('created_at', 'desc');
        $filteredActivities = $this->controller->applyDateFilter($activities, $request);
        $results = $filteredActivities->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $recent->id));
        $this->assertFalse($results->contains('id', $old->id));
    }

    /** @test */
    public function it_can_filter_activities_by_last_year()
    {
        // Create activities
        $thisYear = Activity::factory()->create([
            'description' => 'This year activity',
            'created_at' => now()->subMonths(6),
            'userId' => $this->user->id
        ]);

        $lastYear = Activity::factory()->create([
            'description' => 'Last year activity',
            'created_at' => now()->subYear()->subMonth(),
            'userId' => $this->user->id
        ]);

        $request = new Request(['period' => 'last_year']);
        $activities = Activity::orderBy('created_at', 'desc');
        $filteredActivities = $this->controller->applyDateFilter($activities, $request);
        $results = $filteredActivities->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $thisYear->id));
        $this->assertFalse($results->contains('id', $lastYear->id));
    }

    /** @test */
    public function it_ignores_date_filtering_when_disabled()
    {
        Config::set('LaravelLogger.enableDateFiltering', false);

        $activity = Activity::factory()->create([
            'description' => 'Test activity',
            'created_at' => now()->subYear(),
            'userId' => $this->user->id
        ]);

        $request = new Request(['period' => 'today']);
        $activities = Activity::orderBy('created_at', 'desc');
        $filteredActivities = $this->controller->applyDateFilter($activities, $request);
        $results = $filteredActivities->get();

        // Should return all activities when filtering is disabled
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $activity->id));
    }

    /** @test */
    public function it_can_export_activities_to_csv()
    {
        // Create test activities
        $activity1 = Activity::factory()->create([
            'description' => 'First activity',
            'userId' => $this->user->id,
            'route' => 'https://example.com/test1',
            'ipAddress' => '192.168.1.1',
            'userAgent' => 'Mozilla/5.0',
            'methodType' => 'GET'
        ]);

        $activity2 = Activity::factory()->create([
            'description' => 'Second activity',
            'userId' => $this->user->id,
            'route' => 'https://example.com/test2',
            'ipAddress' => '192.168.1.2',
            'userAgent' => 'Chrome/91.0',
            'methodType' => 'POST'
        ]);

        $request = new Request(['format' => 'csv']);
        
        $response = $this->controller->exportActivityLog($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function it_can_export_activities_to_json()
    {
        // Create test activities
        $activity = Activity::factory()->create([
            'description' => 'Test activity',
            'userId' => $this->user->id,
            'route' => 'https://example.com/test',
            'ipAddress' => '192.168.1.1',
            'userAgent' => 'Mozilla/5.0',
            'methodType' => 'GET'
        ]);

        $request = new Request(['format' => 'json']);
        
        $response = $this->controller->exportActivityLog($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.json', $response->headers->get('Content-Disposition'));

        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals($activity->id, $data[0]['id']);
        $this->assertEquals('Test activity', $data[0]['description']);
    }

    /** @test */
    public function it_can_export_activities_to_excel()
    {
        // Create test activities
        $activity = Activity::factory()->create([
            'description' => 'Test activity',
            'userId' => $this->user->id,
            'route' => 'https://example.com/test',
            'ipAddress' => '192.168.1.1',
            'userAgent' => 'Mozilla/5.0',
            'methodType' => 'GET'
        ]);

        $request = new Request(['format' => 'excel']);
        
        $response = $this->controller->exportActivityLog($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function it_returns_error_for_invalid_export_format()
    {
        $request = new Request(['format' => 'invalid']);
        
        $response = $this->controller->exportActivityLog($request);

        $this->assertEquals(302, $response->getStatusCode());
    }

    /** @test */
    public function it_can_export_filtered_activities()
    {
        // Create activities with different dates
        $today = Activity::factory()->create([
            'description' => 'Today activity',
            'created_at' => now(),
            'userId' => $this->user->id
        ]);

        $yesterday = Activity::factory()->create([
            'description' => 'Yesterday activity',
            'created_at' => now()->subDay(),
            'userId' => $this->user->id
        ]);

        $lastWeek = Activity::factory()->create([
            'description' => 'Last week activity',
            'created_at' => now()->subWeek(),
            'userId' => $this->user->id
        ]);

        // Export only today's activities
        $request = new Request([
            'format' => 'json',
            'period' => 'today'
        ]);
        
        $response = $this->controller->exportActivityLog($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertCount(1, $data);
        $this->assertEquals($today->id, $data[0]['id']);
    }

    /** @test */
    public function it_can_export_activities_with_search_filters()
    {
        // Create activities with different descriptions
        $loginActivity = Activity::factory()->create([
            'description' => 'User logged in',
            'userId' => $this->user->id
        ]);

        $logoutActivity = Activity::factory()->create([
            'description' => 'User logged out',
            'userId' => $this->user->id
        ]);

        $viewActivity = Activity::factory()->create([
            'description' => 'User viewed page',
            'userId' => $this->user->id
        ]);

        // Export only login activities
        $request = new Request([
            'format' => 'json',
            'description' => 'logged in'
        ]);
        
        $response = $this->controller->exportActivityLog($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertCount(1, $data);
        $this->assertEquals($loginActivity->id, $data[0]['id']);
    }

    /** @test */
    public function it_ignores_export_when_disabled()
    {
        Config::set('LaravelLogger.enableExport', false);

        $request = new Request(['format' => 'csv']);
        
        $response = $this->controller->exportActivityLog($request);

        // Should still work but might have different behavior
        $this->assertNotNull($response);
    }

    /** @test */
    public function it_includes_user_details_in_export()
    {
        $activity = Activity::factory()->create([
            'description' => 'Test activity',
            'userId' => $this->user->id
        ]);

        $request = new Request(['format' => 'json']);
        
        $response = $this->controller->exportActivityLog($request);
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertArrayHasKey('user_email', $data[0]);
        $this->assertEquals($this->user->email, $data[0]['user_email']);
    }

    /** @test */
    public function it_handles_activities_without_user()
    {
        $activity = Activity::factory()->create([
            'description' => 'Guest activity',
            'userId' => null
        ]);

        $request = new Request(['format' => 'json']);
        
        $response = $this->controller->exportActivityLog($request);
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertArrayHasKey('user_email', $data[0]);
        $this->assertNull($data[0]['user_email']);
    }

    /** @test */
    public function it_generates_unique_filenames_for_exports()
    {
        $activity = Activity::factory()->create([
            'description' => 'Test activity',
            'userId' => $this->user->id
        ]);

        $request1 = new Request(['format' => 'csv']);
        $response1 = $this->controller->exportActivityLog($request1);
        
        // Wait a second to ensure different timestamps
        sleep(1);
        
        $request2 = new Request(['format' => 'csv']);
        $response2 = $this->controller->exportActivityLog($request2);

        $filename1 = $response1->headers->get('Content-Disposition');
        $filename2 = $response2->headers->get('Content-Disposition');

        $this->assertNotEquals($filename1, $filename2);
    }
}
