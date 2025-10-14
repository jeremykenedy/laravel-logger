<?php

namespace jeremykenedy\LaravelLogger\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use jeremykenedy\LaravelLogger\App\Http\Controllers\LaravelLoggerController;
use jeremykenedy\LaravelLogger\App\Models\Activity;
use jeremykenedy\LaravelLogger\Tests\TestCase;

class ExportFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new LaravelLoggerController();
        
        $this->user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
        
        Config::set('LaravelLogger.defaultActivityModel', Activity::class);
        Config::set('LaravelLogger.defaultUserModel', \App\Models\User::class);
        Config::set('LaravelLogger.defaultUserIDField', 'id');
        Config::set('LaravelLogger.enableExport', true);
        Config::set('LaravelLogger.enableDateFiltering', true);
        Config::set('LaravelLogger.enableSearch', true);
    }

    /** @test */
    public function it_exports_activities_to_csv_format()
    {
        $activity = Activity::factory()->create([
            'description' => 'Test activity',
            'details' => 'Test details',
            'userType' => 'Registered',
            'userId' => $this->user->id,
            'route' => 'https://example.com/test',
            'ipAddress' => '192.168.1.1',
            'userAgent' => 'Mozilla/5.0',
            'locale' => 'en',
            'referer' => 'https://google.com',
            'methodType' => 'GET'
        ]);

        $request = new Request(['format' => 'csv']);
        
        $response = $this->controller->exportActivityLog($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition'));
        
        $content = $response->getContent();
        $this->assertStringContainsString('ID,Description,Details,User Type', $content);
        $this->assertStringContainsString($activity->id, $content);
        $this->assertStringContainsString('Test activity', $content);
        $this->assertStringContainsString('test@example.com', $content);
    }

    /** @test */
    public function it_exports_activities_to_json_format()
    {
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
        $this->assertEquals('test@example.com', $data[0]['user_email']);
        $this->assertEquals('https://example.com/test', $data[0]['route']);
        $this->assertEquals('192.168.1.1', $data[0]['ip_address']);
    }

    /** @test */
    public function it_exports_activities_to_excel_format()
    {
        $activity = Activity::factory()->create([
            'description' => 'Test activity',
            'userId' => $this->user->id
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
    public function it_uses_csv_as_default_format()
    {
        $activity = Activity::factory()->create();

        $request = new Request([]); // No format specified
        
        $response = $this->controller->exportActivityLog($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_exports_multiple_activities()
    {
        $activity1 = Activity::factory()->create([
            'description' => 'First activity',
            'userId' => $this->user->id
        ]);

        $activity2 = Activity::factory()->create([
            'description' => 'Second activity',
            'userId' => $this->user->id
        ]);

        $request = new Request(['format' => 'json']);
        
        $response = $this->controller->exportActivityLog($request);
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertCount(2, $data);
        $this->assertTrue(collect($data)->contains('id', $activity1->id));
        $this->assertTrue(collect($data)->contains('id', $activity2->id));
    }

    /** @test */
    public function it_exports_activities_with_user_details()
    {
        $activity = Activity::factory()->create([
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
        $activity = Activity::factory()->guest()->create();

        $request = new Request(['format' => 'json']);
        
        $response = $this->controller->exportActivityLog($request);
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertArrayHasKey('user_email', $data[0]);
        $this->assertNull($data[0]['user_email']);
    }

    /** @test */
    public function it_includes_additional_details_in_json_export()
    {
        $activity = Activity::factory()->create([
            'userId' => $this->user->id
        ]);

        $request = new Request(['format' => 'json']);
        
        $response = $this->controller->exportActivityLog($request);
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertArrayHasKey('time_passed', $data[0]);
        $this->assertArrayHasKey('user_agent_details', $data[0]);
        $this->assertArrayHasKey('lang_details', $data[0]);
    }

    /** @test */
    public function it_generates_unique_filenames()
    {
        Activity::factory()->create();

        $request1 = new Request(['format' => 'csv']);
        $response1 = $this->controller->exportActivityLog($request1);
        
        sleep(1); // Ensure different timestamps
        
        $request2 = new Request(['format' => 'csv']);
        $response2 = $this->controller->exportActivityLog($request2);

        $filename1 = $response1->headers->get('Content-Disposition');
        $filename2 = $response2->headers->get('Content-Disposition');

        $this->assertNotEquals($filename1, $filename2);
    }

    /** @test */
    public function it_exports_filtered_activities_by_date()
    {
        $today = Activity::factory()->today()->create([
            'description' => 'Today activity'
        ]);

        $yesterday = Activity::factory()->yesterday()->create([
            'description' => 'Yesterday activity'
        ]);

        $request = new Request([
            'format' => 'json',
            'period' => 'today'
        ]);
        
        $response = $this->controller->exportActivityLog($request);
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertCount(1, $data);
        $this->assertEquals($today->id, $data[0]['id']);
    }

    /** @test */
    public function it_exports_filtered_activities_by_search()
    {
        $loginActivity = Activity::factory()->login()->create([
            'userId' => $this->user->id
        ]);

        $logoutActivity = Activity::factory()->logout()->create([
            'userId' => $this->user->id
        ]);

        $request = new Request([
            'format' => 'json',
            'description' => 'logged in'
        ]);
        
        $response = $this->controller->exportActivityLog($request);
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertCount(1, $data);
        $this->assertEquals($loginActivity->id, $data[0]['id']);
    }

    /** @test */
    public function it_handles_empty_activities_gracefully()
    {
        $request = new Request(['format' => 'json']);
        
        $response = $this->controller->exportActivityLog($request);
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertIsArray($data);
        $this->assertCount(0, $data);
    }

    /** @test */
    public function it_preserves_activity_order_in_export()
    {
        $activity1 = Activity::factory()->create([
            'created_at' => now()->subDays(2),
            'description' => 'First activity'
        ]);

        $activity2 = Activity::factory()->create([
            'created_at' => now()->subDays(1),
            'description' => 'Second activity'
        ]);

        $request = new Request(['format' => 'json']);
        
        $response = $this->controller->exportActivityLog($request);
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertCount(2, $data);
        // Should be ordered by created_at desc (newest first)
        $this->assertEquals($activity2->id, $data[0]['id']);
        $this->assertEquals($activity1->id, $data[1]['id']);
    }

    /** @test */
    public function it_includes_all_required_fields_in_csv()
    {
        $activity = Activity::factory()->create([
            'userId' => $this->user->id
        ]);

        $request = new Request(['format' => 'csv']);
        
        $response = $this->controller->exportActivityLog($request);
        $content = $response->getContent();
        
        $expectedHeaders = [
            'ID',
            'Description',
            'Details',
            'User Type',
            'User ID',
            'User Email',
            'Route',
            'IP Address',
            'User Agent',
            'Locale',
            'Referer',
            'Method Type',
            'Created At',
            'Updated At'
        ];

        foreach ($expectedHeaders as $header) {
            $this->assertStringContainsString($header, $content);
        }
    }

    /** @test */
    public function it_includes_all_required_fields_in_json()
    {
        $activity = Activity::factory()->create([
            'userId' => $this->user->id
        ]);

        $request = new Request(['format' => 'json']);
        
        $response = $this->controller->exportActivityLog($request);
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $expectedFields = [
            'id',
            'description',
            'details',
            'user_type',
            'user_id',
            'user_email',
            'route',
            'ip_address',
            'user_agent',
            'locale',
            'referer',
            'method_type',
            'created_at',
            'updated_at',
            'time_passed',
            'user_agent_details',
            'lang_details'
        ];

        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $data[0]);
        }
    }

    /** @test */
    public function it_handles_large_datasets_efficiently()
    {
        // Create 100 activities
        Activity::factory()->count(100)->create([
            'userId' => $this->user->id
        ]);

        $request = new Request(['format' => 'json']);
        
        $startTime = microtime(true);
        $response = $this->controller->exportActivityLog($request);
        $endTime = microtime(true);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        $this->assertCount(100, $data);
        
        // Should complete within reasonable time (less than 5 seconds)
        $this->assertLessThan(5, $endTime - $startTime);
    }
}
