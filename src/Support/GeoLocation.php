<?php

namespace jeremykenedy\LaravelLogger\Support;

use stdClass;

class GeoLocation
{
    public function lookup($ip = null, $purpose = 'location', $deepDetect = true)
    {
        if (! config('LaravelLogger.enableGeoPlugin', true)) {
            return null;
        }

        $ip = $this->address($ip, $deepDetect);
        $purpose = str_replace(['name', "\n", "\t", ' ', '-', '_'], '', strtolower(trim((string) $purpose)));
        if (! filter_var($ip, FILTER_VALIDATE_IP) || ! in_array($purpose, ['country', 'countrycode', 'state', 'region', 'city', 'location', 'address'])) {
            return null;
        }

        $location = $this->fetch($ip);
        if ($location === null) {
            return null;
        }

        return $this->format($location, $purpose);
    }

    private function address($ip, $deepDetect)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        foreach ($deepDetect ? ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP'] : [] as $header) {
            $candidate = $_SERVER[$header] ?? null;
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                $ip = $candidate;
            }
        }

        return $ip;
    }

    private function fetch(string $ip): ?stdClass
    {
        $url = config('LaravelLogger.geoPluginUrl', 'http://www.geoplugin.net/json.gp?ip=');
        $response = @file_get_contents($url.$ip, false, stream_context_create(['http' => ['timeout' => 3]]));
        $location = json_decode((string) $response);

        if (! $location instanceof stdClass) {
            return null;
        }

        return strlen(trim((string) ($location->geoplugin_countryCode ?? ''))) === 2 ? $location : null;
    }

    private function format(stdClass $location, string $purpose)
    {
        if ($purpose === 'location') {
            return $this->details($location);
        }
        if ($purpose === 'address') {
            return $this->fullAddress($location);
        }

        $fields = ['city' => 'city', 'state' => 'regionName', 'region' => 'regionName', 'country' => 'countryName', 'countrycode' => 'countryCode'];

        return $location->{'geoplugin_'.$fields[$purpose]} ?? null;
    }

    private function details(stdClass $location): array
    {
        $fields = [
            'city' => 'city', 'state' => 'regionName', 'country' => 'countryName', 'countryCode' => 'countryCode',
            'continent' => 'continentCode', 'continent_code' => 'continentCode', 'latitude' => 'latitude', 'longitude' => 'longitude',
            'currencyCode' => 'currencyCode', 'areaCode' => 'areaCode', 'dmaCode' => 'dmaCode', 'region' => 'region',
        ];
        $details = [];
        foreach ($fields as $key => $field) {
            $details[$key] = $location->{'geoplugin_'.$field} ?? null;
        }
        $continents = ['AF' => 'Africa', 'AN' => 'Antarctica', 'AS' => 'Asia', 'EU' => 'Europe', 'OC' => 'Australia (Oceania)', 'NA' => 'North America', 'SA' => 'South America'];
        $details['continent'] = $continents[strtoupper((string) $details['continent_code'])] ?? null;

        return $details;
    }

    private function fullAddress(stdClass $location): string
    {
        $parts = [$location->geoplugin_countryName ?? null];
        foreach (['regionName', 'city'] as $field) {
            $part = $location->{'geoplugin_'.$field} ?? null;
            if (strlen((string) $part) >= 1) {
                $parts[] = $part;
            }
        }

        return implode(', ', array_reverse($parts));
    }
}
