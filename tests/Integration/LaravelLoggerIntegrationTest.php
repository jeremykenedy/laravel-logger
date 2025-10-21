<?php

namespace jeremykenedy\LaravelLogger\Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use jeremykenedy\LaravelLogger\App\Models\Activity;
use jeremykenedy\LaravelLogger\Tests\TestCase;

class LaravelLoggerIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'name'  => 'Test User',
        ]);

        Config::set('LaravelLogger.defaultActivityModel', Activity::class);
        Config::set('LaravelLogger.defaultUserModel', \App\Models\User::class);
        Config::set('LaravelLogger.defaultUserIDField', 'id');
        Config::set('LaravelLogger.enableDateFiltering', true);
        Config::set('LaravelLogger.enableExport', true);
        Config::set('LaravelLogger.enableSearch', true);
        Config::set('LaravelLogger.loggerPaginationEnabled', false);
    }

    /** @test */
    public function it_can_access_activity_log_page()
    {
        $this->actingAs($this->user);

        $response = $this->get('/activity');

        $response->assertStatus(200);
        $response->assertViewIs('LaravelLogger::logger.activity-log');
    }

    /** @test */
    public function it_can_filter_activities_via_web_interface()
    {
        $this->actingAs($this->user);

        // Create test activities
        $today = Activity::factory()->today()->create([
            'description' => 'Today activity',
            'userId'      => $this->user->id,
        ]);

        $yesterday = Activity::factory()->yesterday()->create([
            'description' => 'Yesterday activity',
            'userId'      => $this->user->id,
        ]);

        // Test filtering by period
        $response = $this->get('/activity?period=today');

        $response->assertStatus(200);
        $response->assertViewHas('activities');

        $activities = $response->viewData('activities');
        $this->assertCount(1, $activities);
        $this->assertEquals($today->id, $activities->first()->id);
    }

    /** @test */
    public function it_can_export_activities_via_web_interface()
    {
        $this->actingAs($this->user);

        Activity::factory()->create([
            'description' => 'Test activity',
            'userId'      => $this->user->id,
        ]);

        // Test CSV export
        $response = $this->get('/activity/export?format=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', function ($value) {
            return str_contains($value, 'attachment') && str_contains($value, '.csv');
        });
    }

    /** @test */
    public function it_can_export_json_via_web_interface()
    {
        $this->actingAs($this->user);

        $activity = Activity::factory()->create([
            'description' => 'Test activity',
            'userId'      => $this->user->id,
        ]);

        $response = $this->get('/activity/export?format=json');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');

        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals($activity->id, $data[0]['id']);
    }

    /** @test */
    public function it_can_export_excel_via_web_interface()
    {
        $this->actingAs($this->user);

        Activity::factory()->create([
            'description' => 'Test activity',
            'userId'      => $this->user->id,
        ]);

        $response = $this->get('/activity/export?format=excel');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', function ($value) {
            return str_contains($value, 'attachment') && str_contains($value, '.xlsx');
        });
    }

    /** @test */
    public function it_can_combine_filtering_and_export()
    {
        $this->actingAs($this->user);

        $today = Activity::factory()->today()->create([
            'description' => 'Today activity',
            'userId'      => $this->user->id,
        ]);

        $yesterday = Activity::factory()->yesterday()->create([
            'description' => 'Yesterday activity',
            'userId'      => $this->user->id,
        ]);

        // Export only today's activities
        $response = $this->get('/activity/export?format=json&period=today');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertEquals($today->id, $data[0]['id']);
    }

    /** @test */
    public function it_can_search_and_export_activities()
    {
        $this->actingAs($this->user);

        $loginActivity = Activity::factory()->login()->create([
            'userId' => $this->user->id,
        ]);

        $logoutActivity = Activity::factory()->logout()->create([
            'userId' => $this->user->id,
        ]);

        // Export only login activities
        $response = $this->get('/activity/export?format=json&description=logged in');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertEquals($loginActivity->id, $data[0]['id']);
    }

    /** @test */
    public function it_handles_unauthorized_access()
    {
        // Test without authentication
        $response = $this->get('/activity');

        $response->assertStatus(302); // Redirect to login
    }

    /** @test */
    public function it_handles_unauthorized_export()
    {
        // Test export without authentication
        $response = $this->get('/activity/export?format=csv');

        $response->assertStatus(302); // Redirect to login
    }

    /** @test */
    public function it_can_view_individual_activity()
    {
        $this->actingAs($this->user);

        $activity = Activity::factory()->create([
            'userId' => $this->user->id,
        ]);

        $response = $this->get("/activity/log/{$activity->id}");

        $response->assertStatus(200);
        $response->assertViewIs('LaravelLogger::logger.activity-log-item');
        $response->assertViewHas('activity', $activity);
    }

    /** @test */
    public function it_can_view_cleared_activities()
    {
        $this->actingAs($this->user);

        $activity = Activity::factory()->create([
            'userId' => $this->user->id,
        ]);

        $activity->delete(); // Soft delete

        $response = $this->get('/activity/cleared');

        $response->assertStatus(200);
        $response->assertViewIs('LaravelLogger::logger.activity-log-cleared');
    }

    /** @test */
    public function it_can_filter_cleared_activities()
    {
        $this->actingAs($this->user);

        $today = Activity::factory()->today()->create([
            'userId' => $this->user->id,
        ]);

        $yesterday = Activity::factory()->yesterday()->create([
            'userId' => $this->user->id,
        ]);

        $today->delete();
        $yesterday->delete();

        $response = $this->get('/activity/cleared?period=today');

        $response->assertStatus(200);
        $response->assertViewHas('activities');

        $activities = $response->viewData('activities');
        $this->assertCount(1, $activities);
        $this->assertEquals($today->id, $activities->first()->id);
    }

    /** @test */
    public function it_can_clear_activity_log()
    {
        $this->actingAs($this->user);

        Activity::factory()->count(5)->create([
            'userId' => $this->user->id,
        ]);

        $this->assertDatabaseCount('laravel_logger_activity', 5);

        $response = $this->delete('/activity/clear-activity');

        $response->assertStatus(302);
        $response->assertRedirect('/activity');

        // Activities should be soft deleted
        $this->assertDatabaseCount('laravel_logger_activity', 0);
        $this->assertDatabaseCount('laravel_logger_activity', 5, 'deleted_at');
    }

    /** @test */
    public function it_can_restore_cleared_activities()
    {
        $this->actingAs($this->user);

        $activity = Activity::factory()->create([
            'userId' => $this->user->id,
        ]);

        $activity->delete();

        $this->assertDatabaseCount('laravel_logger_activity', 0);

        $response = $this->post('/activity/restore-log');

        $response->assertStatus(302);
        $response->assertRedirect('/activity');

        $this->assertDatabaseCount('laravel_logger_activity', 1);
    }

    /** @test */
    public function it_can_destroy_cleared_activities()
    {
        $this->actingAs($this->user);

        $activity = Activity::factory()->create([
            'userId' => $this->user->id,
        ]);

        $activity->delete();

        $this->assertDatabaseCount('laravel_logger_activity', 0);
        $this->assertDatabaseCount('laravel_logger_activity', 1, 'deleted_at');

        $response = $this->delete('/activity/destroy-activity');

        $response->assertStatus(302);
        $response->assertRedirect('/activity');

        // Activities should be permanently deleted
        $this->assertDatabaseCount('laravel_logger_activity', 0);
        $this->assertDatabaseCount('laravel_logger_activity', 0, 'deleted_at');
    }

    /** @test */
    public function it_can_perform_live_search()
    {
        $this->actingAs($this->user);

        $user1 = \App\Models\User::factory()->create(['email' => 'user1@example.com']);
        $user2 = \App\Models\User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->post('/activity/live-search', [
            'email' => 'user1',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['*' => ['email']]);

        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertArrayHasKey($user1->id, $data);
        $this->assertEquals('user1@example.com', $data[$user1->id]);
    }

    /** @test */
    public function it_handles_invalid_activity_id()
    {
        $this->actingAs($this->user);

        $response = $this->get('/activity/log/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_handles_invalid_cleared_activity_id()
    {
        $this->actingAs($this->user);

        $response = $this->get('/activity/cleared/log/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_respects_configuration_settings()
    {
        // Disable date filtering
        Config::set('LaravelLogger.enableDateFiltering', false);

        $this->actingAs($this->user);

        $activity = Activity::factory()->yesterday()->create([
            'userId' => $this->user->id,
        ]);

        $response = $this->get('/activity?period=today');

        $response->assertStatus(200);
        $response->assertViewHas('activities');

        $activities = $response->viewData('activities');
        // Should return all activities when filtering is disabled
        $this->assertCount(1, $activities);
        $this->assertEquals($activity->id, $activities->first()->id);
    }

    /** @test */
    public function it_handles_pagination_when_enabled()
    {
        Config::set('LaravelLogger.loggerPaginationEnabled', true);
        Config::set('LaravelLogger.loggerPaginationPerPage', 2);

        $this->actingAs($this->user);

        Activity::factory()->count(5)->create([
            'userId' => $this->user->id,
        ]);

        $response = $this->get('/activity');

        $response->assertStatus(200);
        $response->assertViewHas('activities');

        $activities = $response->viewData('activities');
        $this->assertCount(2, $activities); // Only 2 per page
        $this->assertTrue($activities->hasPages());
    }

    /** @test */
    public function it_handles_search_when_disabled()
    {
        Config::set('LaravelLogger.enableSearch', false);

        $this->actingAs($this->user);

        $activity = Activity::factory()->create([
            'description' => 'Test activity',
            'userId'      => $this->user->id,
        ]);

        $response = $this->get('/activity?description=Test');

        $response->assertStatus(200);
        $response->assertViewHas('activities');

        $activities = $response->viewData('activities');
        // Should return all activities when search is disabled
        $this->assertCount(1, $activities);
        $this->assertEquals($activity->id, $activities->first()->id);
    }
}
