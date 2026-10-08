<?php

namespace jeremykenedy\LaravelLogger\App\Http\Traits;

use jeremykenedy\LaravelLogger\Support\UserAgentParser;

trait UserAgentDetails
{
    public static function details($ua)
    {
        return (new UserAgentParser)->parse($ua);
    }

    public static function localeLang($locale)
    {
        return (new UserAgentParser)->locale($locale);
    }
}
