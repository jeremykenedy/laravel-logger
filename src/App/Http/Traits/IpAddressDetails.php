<?php

namespace jeremykenedy\LaravelLogger\App\Http\Traits;

trait IpAddressDetails
{
    /**
     * Get the Location of the IP Address.
     *
     * @param string $ip          (optional, no value will always return NULL)
     * @param string $purpose     (optional)
     * @param bool   $deep_detect (optional)
     *
     * @return string
     */
    public static function checkIP($ip = null, $purpose = 'location', $deep_detect = true)
    {
        $output = null;
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $ip = $_SERVER['REMOTE_ADDR'];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                }
            }
        }
        $purpose = str_replace(['name', "\n", "\t", ' ', '-', '_'], null, strtolower(trim($purpose)));
        $support = ['country', 'countrycode', 'state', 'region', 'city', 'location', 'address'];
        $continents = [
            'AF' => 'Africa',
            'AN' => 'Antarctica',
            'AS' => 'Asia',
            'EU' => 'Europe',
            'OC' => 'Australia (Oceania)',
            'NA' => 'North America',
            'SA' => 'South America',
        ];
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip='.$ip));
            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case 'location':
                        $output = [
                            'city'           => @$ipdat->geoplugin_city,
                            'state'          => @$ipdat->geoplugin_regionName,
                            'country'        => @$ipdat->geoplugin_countryName,
                            'countryCode'    => @$ipdat->geoplugin_countryCode,
                            'continent'      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            'continent_code' => @$ipdat->geoplugin_continentCode,
                            'latitude'       => @$ipdat->geoplugin_latitude,
                            'longitude'      => @$ipdat->geoplugin_longitude,
                            'currencyCode'   => @$ipdat->geoplugin_currencyCode,
                            'areaCode'       => @$ipdat->geoplugin_areaCode,
                            'dmaCode'        => @$ipdat->geoplugin_dmaCode,
                            'region'         => @$ipdat->geoplugin_region,
                        ];
                        break;
                    case 'address':
                        $address = [$ipdat->geoplugin_countryName];
                        if (@strlen($ipdat->geoplugin_regionName) >= 1) {
                            $address[] = $ipdat->geoplugin_regionName;
                        }
                        if (@strlen($ipdat->geoplugin_city) >= 1) {
                            $address[] = $ipdat->geoplugin_city;
                        }
                        $output = implode(', ', array_reverse($address));
                        break;
                    case 'city':
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case 'state':
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case 'region':
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case 'country':
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case 'countrycode':
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                }
            }
        }

        return $output;
    }
}
