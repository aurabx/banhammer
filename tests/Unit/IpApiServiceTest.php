<?php

namespace Mchev\Banhammer\Tests\Unit;

use Mchev\Banhammer\Services\IpApiService;
use Mchev\Banhammer\Tests\TestCase;

class IpApiServiceTest extends TestCase
{
    /** @test */
    public function it_uses_the_free_http_endpoint_when_no_api_key_is_configured(): void
    {
        config(['ban.ip_api.key' => null]);

        $endpoint = (new IpApiService())->endpointFor('124.148.115.68');

        $this->assertSame(
            'http://ip-api.com/json/124.148.115.68?fields=status,message,countryCode,query',
            $endpoint,
        );
    }

    /** @test */
    public function it_uses_the_pro_https_endpoint_when_an_api_key_is_configured(): void
    {
        config(['ban.ip_api.key' => 'secret-key']);

        $endpoint = (new IpApiService())->endpointFor('124.148.115.68');

        $this->assertSame(
            'https://pro.ip-api.com/json/124.148.115.68?fields=status,message,countryCode,query&key=secret-key',
            $endpoint,
        );
    }

    /** @test */
    public function it_url_encodes_the_api_key(): void
    {
        config(['ban.ip_api.key' => 'a b/c']);

        $endpoint = (new IpApiService())->endpointFor('1.2.3.4');

        $this->assertStringEndsWith('key=a+b%2Fc', $endpoint);
    }
}
