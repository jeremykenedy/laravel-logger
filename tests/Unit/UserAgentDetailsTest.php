<?php

namespace jeremykenedy\LaravelLogger\Tests\Unit;

use jeremykenedy\LaravelLogger\App\Http\Traits\UserAgentDetails;
use PHPUnit\Framework\TestCase;

class UserAgentDetailsTest extends TestCase
{
    use UserAgentDetails;

    public function test_browser_metadata_matches_existing_desktop_mobile_and_crawler_samples(): void
    {
        $samples = json_decode(file_get_contents(__DIR__.'/../fixtures/user-agents.json'), true);
        foreach ($samples as $sample) {
            $this->assertSame($sample['details'], self::details($sample['agent']), $sample['agent']);
        }
    }

    public function test_malformed_user_agents_return_unknown_fields(): void
    {
        foreach (['', 'broken|header', 'one|two|three|four'] as $agent) {
            $this->assertSame(['platform' => '-', 'type' => '-', 'renderer' => '-', 'browser' => '-', 'version' => '-'], self::details($agent));
        }
    }

    public function test_null_user_agent_uses_the_request_header(): void
    {
        $original = $_SERVER['HTTP_USER_AGENT'] ?? null;
        try {
            $_SERVER['HTTP_USER_AGENT'] = 'broken|header';
            $this->assertSame('-', self::details(null)['browser']);
            unset($_SERVER['HTTP_USER_AGENT']);
            $this->assertSame('-', self::details(null)['browser']);
        } finally {
            if ($original !== null) {
                $_SERVER['HTTP_USER_AGENT'] = $original;
            }
        }
    }
}
