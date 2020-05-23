<?php

if (!function_exists('showCleanRoutUrl')) {
    /**
     * Clean the url for the front end to display.
     *
     * @param string $link
     *
     * @return echo string
     */
    function showCleanRoutUrl($link)
    {
        $parsedUrl = parse_url($link);
        $routeUrl = '';
        if (isset($parsedUrl['path'])) {
            $routeUrl .= $parsedUrl['path'];
        }
        if (isset($parsedUrl['query'])) {
            $routeUrl .= '?'.$parsedUrl['query'];
        }
        echo $routeUrl;
    }
}
