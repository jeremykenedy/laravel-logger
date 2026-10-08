<?php

if (! function_exists('showCleanRoutUrl')) {
    function showCleanRoutUrl($link): void
    {
        $parsedUrl = parse_url((string) $link);
        $routeUrl = '';
        if (isset($parsedUrl['path'])) {
            $routeUrl .= $parsedUrl['path'];
        }
        if (isset($parsedUrl['query'])) {
            $routeUrl .= '?'.$parsedUrl['query'];
        }
        echo e($routeUrl);
    }
}
