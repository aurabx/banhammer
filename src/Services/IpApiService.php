<?php

namespace Mchev\Banhammer\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpApiService
{
    /**
     * Get geolocation data for an IP address from the IP-API service.
     *
     * The free ip-api.com endpoint only accepts plain HTTP; HTTPS is a paid
     * ("pro") feature that requires an API key. When `ban.ip_api.key` is set
     * we hit `pro.ip-api.com` over HTTPS with the key attached, otherwise we
     * fall back to the free HTTP endpoint.
     */
    public function getGeolocationData(string $ip): ?array
    {
        $cacheKey = 'ip_geolocation_'.$ip;

        try {
            return Cache::remember($cacheKey, now()->addDay(), function () use ($ip) {
                $response = Http::get($this->endpointFor($ip));

                return $response->json();
            });
        } catch (\Exception $e) {
            // Log the error
            Log::error('IP-API Service Error: '.$e->getMessage(), ['exception' => $e]);

            // Handle the error as needed
            return null;
        }
    }

    public function endpointFor(string $ip): string
    {
        $fields = 'status,message,countryCode,query';
        $key = Config::get('ban.ip_api.key');

        if (is_string($key) && $key !== '') {
            return sprintf(
                'https://pro.ip-api.com/json/%s?fields=%s&key=%s',
                $ip,
                $fields,
                urlencode($key),
            );
        }

        return sprintf('http://ip-api.com/json/%s?fields=%s', $ip, $fields);
    }
}
