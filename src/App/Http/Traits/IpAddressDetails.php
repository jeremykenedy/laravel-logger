<?php

namespace jeremykenedy\LaravelLogger\App\Http\Traits;

use jeremykenedy\LaravelLogger\Support\GeoLocation;

trait IpAddressDetails
{
    public static function checkIP($ip = null, $purpose = 'location', $deep_detect = true)
    {
        return (new GeoLocation)->lookup($ip, $purpose, $deep_detect);
    }
}
