<?php

namespace jeremykenedy\LaravelLogger\Tests\Feature;

use Illuminate\Http\Request;
use Jaybizzle\LaravelCrawlerDetect\Facades\LaravelCrawlerDetect;
use Jaybizzle\LaravelCrawlerDetect\LaravelCrawlerDetectServiceProvider;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;
use jeremykenedy\LaravelLogger\App\Models\Activity;
use jeremykenedy\LaravelLogger\CrawlerDetectServiceProvider;
use jeremykenedy\LaravelLogger\Facades\Crawler;
use jeremykenedy\LaravelLogger\Support\CrawlerDetect;
use jeremykenedy\LaravelLogger\Support\CrawlerPatterns;
use jeremykenedy\LaravelLogger\Tests\TestCase;

class CrawlerDetectionTest extends TestCase
{
    public function test_bundled_detector_classifies_the_reference_user_agents_and_client_hints(): void
    {
        $detector = new CrawlerDetect;
        foreach (['user_agent', 'sec_ch_ua'] as $header) {
            foreach (['crawlers' => true, 'devices' => false] as $group => $expected) {
                $path = __DIR__.'/../fixtures/crawlers/'.$header.'/'.$group.'.txt';
                $agents = is_file($path) ? file($path) : gzfile($path.'.gz');
                foreach ($agents as $agent) {
                    $agent = rtrim($agent, "\r\n");
                    if ($agent === '') {
                        continue;
                    }
                    $this->assertSame($expected, $detector->isCrawler($agent), $agent);
                }
            }
        }
    }

    public function test_alternative_headers_identify_crawlers_using_browser_user_agents(): void
    {
        $detector = new CrawlerDetect(['HTTP_USER_AGENT' => 'Mozilla/5.0 Safari/537.36', 'HTTP_FROM' => 'googlebot(at)googlebot.com']);
        $this->assertTrue($detector->isCrawler());
        $this->assertSame('googlebot', $detector->getMatches());
        $detector->setHttpHeaders(['HTTP_SEC_CH_UA' => '"HeadlessChrome";v="131"']);
        $detector->setUserAgent();
        $this->assertTrue($detector->isCrawler());
        $detector->setHttpHeaders(['HTTP_USER_AGENT' => 'Mozilla/5.0', 'SERVER_NAME' => 'googlebot.com']);
        $detector->setUserAgent();
        $this->assertFalse($detector->isCrawler());
    }

    public function test_matches_reset_and_explicit_checks_do_not_change_the_stored_agent(): void
    {
        $detector = new CrawlerDetect(null, 'Mozilla/5.0');
        $this->assertTrue($detector->isCrawler('SomeCrawlerBot/1.0'));
        $this->assertSame('SomeCrawlerBot', $detector->getMatches());
        $this->assertSame('Mozilla/5.0', $detector->getUserAgent());
        $this->assertFalse($detector->isCrawler('iPod'));
        $this->assertNull($detector->getMatches());
        $this->assertFalse($detector->isCrawler('   '));
        $this->assertFalse($detector->isCrawler());
        $this->assertFalse($detector->isCrawler('Amazon CloudFront'));
        $this->assertSame('', $detector->setUserAgent(''));
        $this->assertFalse($detector->isCrawler());
    }

    public function test_missing_headers_and_regex_failure_do_not_report_a_crawler(): void
    {
        $detector = new CrawlerDetect(['SERVER_NAME' => 'localhost']);
        $this->assertNull($detector->getUserAgent());
        $this->assertFalse($detector->isCrawler());
        $limit = ini_get('pcre.backtrack_limit');
        try {
            ini_set('pcre.backtrack_limit', '1');
            $this->assertFalse($detector->isCrawler('Mozilla/5.0 (compatible; Googlebot/2.1)'));
            $this->assertNull($detector->getMatches());
        } finally {
            ini_set('pcre.backtrack_limit', $limit);
        }
    }

    public function test_logging_keeps_crawler_fields_and_refreshes_detection_between_requests(): void
    {
        $logger = new class
        {
            use ActivityLogger;
        };
        $this->app->instance('request', Request::create('http://localhost/bot', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'Googlebot/2.1']));
        $logger->activity();
        $activity = Activity::first();
        $this->assertSame('Crawler', $activity->userType);
        $this->assertSame('Crawler crawled http://localhost/bot', $activity->description);
        $this->assertSame('Googlebot/2.1', $activity->userAgent);
        $this->assertNull($activity->userId);
        $this->app->instance('request', Request::create('http://localhost/browser', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'Mozilla/5.0']));
        $logger->activity();
        $this->assertSame('Guest', Activity::orderBy('id', 'desc')->first()->userType);
        $user = $this->createUser();
        $this->actingAs($user);
        $this->app->instance('request', Request::create('http://localhost/authenticated-bot', 'GET', [], [], [], ['HTTP_FROM' => 'googlebot(at)googlebot.com']));
        $logger->activity('Custom description');
        $activity = Activity::orderBy('id', 'desc')->first();
        $this->assertSame('Crawler', $activity->userType);
        $this->assertEquals($user->id, $activity->userId);
        $this->assertSame('Custom description', $activity->description);
    }

    public function test_existing_container_overrides_are_preserved(): void
    {
        $custom = new class
        {
            public function isCrawler(): bool
            {
                return true;
            }
        };
        $this->app->instance('LaravelCrawlerDetect', $custom);
        (new CrawlerDetectServiceProvider($this->app))->register();
        $this->app->instance('request', Request::create('/custom'));
        $this->assertSame($custom, Crawler::getFacadeRoot());
        $this->assertTrue(Crawler::isCrawler());
    }

    public function test_detector_subclasses_can_customize_headers_and_matching_without_changing_other_instances(): void
    {
        $detector = new class(['HTTP_CUSTOM_AGENT' => 'CompanyMonitor/1.0']) extends CrawlerDetect
        {
            public function getUaHttpHeaders()
            {
                return ['HTTP_CUSTOM_AGENT'];
            }

            public function compileRegex($patterns)
            {
                return parent::compileRegex($patterns === CrawlerPatterns::CRAWLERS ? array_merge($patterns, ['CompanyMonitor']) : $patterns);
            }

            public function restrictToCrawler($pattern)
            {
                $this->compiledRegex = $this->compileRegex([$pattern]);
            }
        };
        $this->assertTrue($detector->isCrawler());
        $this->assertSame('CompanyMonitor', $detector->getMatches());
        $detector->restrictToCrawler('SpecialSpider');
        $this->assertTrue($detector->isCrawler('SpecialSpider/1.0'));
        $this->assertFalse($detector->isCrawler('Googlebot/2.1'));
        $this->assertFalse((new CrawlerDetect)->isCrawler('CompanyMonitor/1.0'));
        $this->assertTrue((new CrawlerDetect)->isCrawler('Googlebot/2.1'));
    }

    public function test_legacy_provider_facade_and_detector_names_remain_available(): void
    {
        $provider = $this->app->register(LaravelCrawlerDetectServiceProvider::class);
        $this->assertInstanceOf(CrawlerDetectServiceProvider::class, $provider);
        $this->app->instance('request', Request::create('/crawler', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'Googlebot/2.1']));
        $this->assertTrue(LaravelCrawlerDetect::isCrawler());
        $detector = new \Jaybizzle\CrawlerDetect\CrawlerDetect(null, 'Bingbot/2.0');
        $this->assertInstanceOf(CrawlerDetect::class, $detector);
        $this->assertTrue($detector->isCrawler());
    }
}
