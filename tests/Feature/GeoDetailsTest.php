<?php

namespace jeremykenedy\LaravelLogger\Tests\Feature;

use jeremykenedy\LaravelLogger\App\Http\Traits\IpAddressDetails;
use jeremykenedy\LaravelLogger\Tests\TestCase;

class GeoDetailsTest extends TestCase
{
    private $prefix;

    protected function setUp(): void
    {
        $this->prefix = sys_get_temp_dir().'/logger_geo_'.uniqid().'_';
        parent::setUp();
        config(['LaravelLogger.enableGeoPlugin' => true, 'LaravelLogger.geoPluginUrl' => 'file://'.$this->prefix]);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->prefix.'*') as $file) {
            unlink($file);
        }
        parent::tearDown();
    }

    public function test_location_fields_and_existing_purpose_aliases_are_preserved(): void
    {
        file_put_contents($this->prefix.'192.0.2.1', json_encode([
            'geoplugin_city' => 'Portland', 'geoplugin_regionName' => 'Oregon',
            'geoplugin_countryName' => 'United States', 'geoplugin_countryCode' => 'US',
            'geoplugin_continentCode' => 'NA', 'geoplugin_latitude' => '45.52',
            'geoplugin_longitude' => '-122.68', 'geoplugin_currencyCode' => 'USD',
            'geoplugin_areaCode' => '503', 'geoplugin_dmaCode' => '820', 'geoplugin_region' => 'OR',
        ]));
        $location = IpAddressDetails::checkIP('192.0.2.1');
        $this->assertSame('Portland', $location['city']);
        $this->assertSame('North America', $location['continent']);
        $this->assertSame('45.52', $location['latitude']);
        $this->assertSame('USD', $location['currencyCode']);
        foreach (['city' => 'Portland', 'state' => 'Oregon', 'region' => 'Oregon', 'country name' => 'United States', 'country-code' => 'US', 'address' => 'Portland, Oregon, United States'] as $purpose => $expected) {
            $this->assertSame($expected, IpAddressDetails::checkIP('192.0.2.1', $purpose));
        }
    }

    public function test_missing_malformed_and_incomplete_responses_return_null(): void
    {
        $this->assertNull(IpAddressDetails::checkIP('192.0.2.1'));
        file_put_contents($this->prefix.'192.0.2.1', 'not JSON');
        $this->assertNull(IpAddressDetails::checkIP('192.0.2.1'));
        file_put_contents($this->prefix.'192.0.2.1', '{}');
        $this->assertNull(IpAddressDetails::checkIP('192.0.2.1'));
        $this->assertNull(IpAddressDetails::checkIP('192.0.2.1', 'unsupported'));
    }

    public function test_disabled_lookup_and_missing_request_address_are_safe(): void
    {
        config(['LaravelLogger.enableGeoPlugin' => false]);
        $this->assertNull(IpAddressDetails::checkIP('192.0.2.1'));
        config(['LaravelLogger.enableGeoPlugin' => true]);
        $original = $_SERVER['REMOTE_ADDR'] ?? null;
        unset($_SERVER['REMOTE_ADDR']);
        try {
            $this->assertNull(IpAddressDetails::checkIP(null, 'location', false));
        } finally {
            if ($original !== null) {
                $_SERVER['REMOTE_ADDR'] = $original;
            }
        }
    }
}
