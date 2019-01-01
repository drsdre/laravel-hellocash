<?php

namespace drsdre\HelloCash\Test\Unit;

use drsdre\HelloCash\HelloCashClient;
use drsdre\HelloCash\HelloCashServiceProvider;
use drsdre\HelloCash\Test\TestCase;
use InvalidArgumentException;

class ServiceProviderTest extends TestCase
{
    /**
     * @test
     * @group unit
     */
    public function it_is_deferred()
    {
        $provider = $this->app->getProvider(HelloCashServiceProvider::class);

        $this->assertTrue($provider->isDeferred());
    }

    /**
     * @test
     * @group unit
     */
    public function it_merges_the_configuration()
    {
        $config = $this->app['config']->get('service.hellocash');

        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('principal', $config);
        $this->assertArrayHasKey('credentials', $config);
        $this->assertArrayHasKey('system', $config);
    }

    /**
     * @test
     * @group unit
     */
    public function it_provides_the_client()
    {
        $provider = $this->app->getProvider(HelloCashServiceProvider::class);

        $this->assertContains(HelloCashClient::class, $provider->provides());
    }

    /**
     * @test
     * @group unit
     */
    public function it_resolves_the_client_as_a_singleton()
    {
        $client = $this->app->make(HelloCashClient::class);

        $this->assertInstanceof(HelloCashClient::class, $client);
        $this->assertTrue($this->app->isShared(HelloCashClient::class));
    }

    /**
     * @test
     * @group unit
     */
    public function it_gets_the_production_url()
    {
        app('config')->set('service.hellocash.system', 'production');

        $url = app(HelloCashClient::class)->getUrl();

        $this->assertEquals(HelloCashClient::PRODUCTION_URL, $url, 'The URL should be ' . HelloCashClient::PRODUCTION_URL);
    }

    /**
     * @test
     * @group unit
     */
    public function it_throws_an_exception_when_the_system_is_invalid()
    {
        $this->expectException(InvalidArgumentException::class);

        app('config')->set('service.hellocash.system', '');

        $url = app(HelloCashClient::class)->getUrl();
    }

    /**
     * @test
     */
    public function it_doesnt_use_tlsv1_for_nss()
    {
        $client = app(HelloCashClient::class);

        $curl = $client->getClient()->getConfig('curl');

        if (preg_match('/NSS/', curl_version()['ssl_version'])) {
            $this->assertEmpty($curl);
        } else {
            $this->assertEquals([CURLOPT_SSL_CIPHER_LIST => 'TLSv1'], $curl);
        }
    }
}
