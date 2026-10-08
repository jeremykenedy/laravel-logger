<?php

namespace jeremykenedy\LaravelLogger\Support;

class CrawlerDetect
{
    protected $httpHeaders = [];

    protected $userAgent;

    protected $matches = [];

    protected $compiledRegex;

    protected $compiledExclusions;

    protected static $crawlerRegex;

    protected static $exclusionRegex;

    public function __construct(?array $headers = null, $userAgent = null)
    {
        if (self::$crawlerRegex === null) {
            self::$crawlerRegex = self::compileRegex(CrawlerPatterns::CRAWLERS);
            self::$exclusionRegex = self::compileRegex(CrawlerPatterns::EXCLUSIONS);
        }

        $this->compiledRegex = get_class($this) === self::class ? self::$crawlerRegex : $this->compileRegex(CrawlerPatterns::CRAWLERS);
        $this->compiledExclusions = get_class($this) === self::class ? self::$exclusionRegex : $this->compileRegex(CrawlerPatterns::EXCLUSIONS);

        $this->setHttpHeaders($headers);
        $this->setUserAgent($userAgent);
    }

    public function compileRegex($patterns)
    {
        return '(?:'.implode('|', $patterns).')';
    }

    public function setHttpHeaders($httpHeaders = null)
    {
        $headers = is_array($httpHeaders) && $httpHeaders !== [] ? $httpHeaders : $_SERVER;
        $this->httpHeaders = array_filter($headers, function ($key) {
            return strpos($key, 'HTTP_') === 0;
        }, ARRAY_FILTER_USE_KEY);
    }

    public function getUaHttpHeaders()
    {
        return CrawlerPatterns::HEADERS;
    }

    public function setUserAgent($userAgent = null)
    {
        if ($userAgent === null) {
            $userAgent = '';
            foreach ($this->getUaHttpHeaders() as $header) {
                if (isset($this->httpHeaders[$header])) {
                    $userAgent .= $this->httpHeaders[$header].' ';
                }
            }
            $userAgent = $userAgent === '' ? null : $userAgent;
        }

        return $this->userAgent = $userAgent;
    }

    public function isCrawler($userAgent = null)
    {
        $this->matches = [];
        $agent = preg_replace('/'.$this->compiledExclusions.'/i', '', $userAgent ?: $this->userAgent ?: '');
        if ($agent === null || trim($agent) === '') {
            return false;
        }

        if (preg_match('/'.$this->compiledRegex.'/i', trim($agent), $this->matches) !== 1) {
            $this->matches = [];

            return false;
        }

        return true;
    }

    public function getMatches()
    {
        return $this->matches[0] ?? null;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }
}
