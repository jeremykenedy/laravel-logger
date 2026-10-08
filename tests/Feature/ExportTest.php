<?php

namespace jeremykenedy\LaravelLogger\Tests\Feature;

use jeremykenedy\LaravelLogger\Tests\TestCase;
use ZipArchive;

class ExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createUser());
    }

    public function test_csv_contains_headers_quoted_values_and_neutralized_formulas(): void
    {
        $this->createActivity(['description' => '=HYPERLINK("http://example.com")', 'details' => "First, second\nthird"]);
        $response = $this->get('/activity/export')->assertOk();
        ob_start();
        $response->baseResponse->sendContent();
        $csv = ob_get_clean();
        $this->assertStringContainsString('Description', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString('First, second', $csv);
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition'));
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $csv);
        rewind($stream);
        fgetcsv($stream, 0, ',', '"', '\\');
        $row = fgetcsv($stream, 0, ',', '"', '\\');
        fclose($stream);
        $this->assertSame("'=HYPERLINK(\"http://example.com\")", $row[1]);
        $this->assertSame("First, second\nthird", $row[2]);
    }

    public function test_json_export_preserves_fields_and_applies_search(): void
    {
        $user = $this->createUser(['email' => 'activity@example.com']);
        $match = $this->createActivity(['description' => 'Saved profile', 'userId' => $user->id]);
        $this->createActivity();
        $response = $this->get('/activity/export?format=json&description=Saved')->assertOk();
        $data = json_decode($response->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertSame($match->id, $data[0]['id']);
        $this->assertSame($user->email, $data[0]['user_email']);
        foreach (['details', 'user_type', 'route', 'ip_address', 'user_agent', 'locale', 'referer', 'method_type', 'created_at', 'updated_at', 'time_passed', 'user_agent_details', 'lang_details'] as $key) {
            $this->assertArrayHasKey($key, $data[0]);
        }
    }

    public function test_excel_is_a_valid_workbook_with_text_cells(): void
    {
        $this->createActivity(['description' => '=SUM(1,2)', 'details' => '<script>& text']);
        $response = $this->get('/activity/export?format=excel')->assertOk();
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($path));
            $this->assertNotFalse($zip->getFromName('[Content_Types].xml'));
            $this->assertNotFalse($zip->getFromName('xl/workbook.xml'));
            $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
            $this->assertNotFalse(simplexml_load_string($sheet));
            $this->assertStringContainsString('=SUM(1,2)', $sheet);
            $this->assertStringContainsString('&lt;script&gt;&amp; text', $sheet);
            $this->assertStringNotContainsString('<f>', $sheet);
            $zip->close();
        } finally {
            unlink($path);
        }
    }

    public function test_export_setting_is_enforced(): void
    {
        config(['LaravelLogger.enableExport' => false]);
        foreach (['csv', 'json', 'excel'] as $format) {
            $this->get('/activity/export?format='.$format)->assertForbidden();
        }
    }

    public function test_date_filters_and_soft_deletes_apply_to_exports(): void
    {
        $match = $this->createActivity();
        $this->createActivity(['created_at' => now()->subWeek()]);
        $deleted = $this->createActivity();
        $deleted->delete();
        $data = $this->get('/activity/export?format=json&period=today')->assertOk()->json();
        $this->assertSame([$match->id], array_column($data, 'id'));
    }

    public function test_invalid_formats_redirect_and_empty_exports_are_valid(): void
    {
        $this->get('/activity/export?format=unknown')->assertRedirect();
        $this->assertSame([], $this->get('/activity/export?format=json')->assertOk()->json());
    }
}
