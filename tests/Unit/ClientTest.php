<?php

namespace drsdre\HelloCash\Test\Unit;

use drsdre\HelloCash\Exceptions\HelloCashException;
use drsdre\HelloCash\HelloCashClient;
use drsdre\HelloCash\Requests\Invoice;
use drsdre\HelloCash\Test\TestCase;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;

class ClientTest extends TestCase
{
    /**
     * @test
     * @group unit
     */
    public function it_gets_the_url_from_guzzle_configuration()
    {
        $client = new HelloCashClient(new GuzzleClient([ 'base_uri' => HelloCashClient::PRODUCTION_URL]));

        $this->assertEquals(HelloCashClient::PRODUCTION_URL, $client->getUrl(), 'The URL should be ' . HelloCashClient::PRODUCTION_URL);
    }

    /**
     * @test
     * @group unit
     */
    public function it_decodes_a_json_response()
    {
        $json = json_encode([
            'ErrorCode' => 0,
            'ErrorText' => 'No errors.',
        ]);

        $this->mockResponses([
            new Response(200, [], $json),
        ]);

        $order = new Invoice($this->client);

        $response = $order->get(42);

        $this->assertEquals(json_decode($json), $response, 'The JSON response was not decoded.');
    }

    /**
     * @test
     * @group unit
     */
    public function it_throws_an_exception()
    {
        $success = [
            'ErrorCode' => 0,
            'ErrorText' => 'No errors.',
        ];

        $failure = [
            'ErrorCode' => 1,
            'ErrorText' => 'Some error occurred.',
        ];

        $this->mockJsonResponses(compact('success', 'failure'));

        $order = new Invoice($this->client);

        $order->get(42);

        $this->expectException(HelloCashException::class);

        $order->get(43);
    }
}
