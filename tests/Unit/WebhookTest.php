<?php

namespace drsdre\HelloCash\Test\Unit;

use drsdre\HelloCash\Connection;
use drsdre\HelloCash\Test\TestCase;

class WebhookTest extends TestCase
{
    /**
     * @test
     * @group unit
     */
    public function it_gets_an_authorization_code()
    {
        $verification = ['foo' => 'bar'];

        $this->mockJsonResponses([$verification]);
        $this->mockRequests();

        $webhook = new Connection($this->client);

        $code = $webhook->getAuthorizationCode();
        $request = $this->getLastRequest();

        $this->assertMethod('GET', $request);
        $this->assertEquals($verification, (array) $code);
    }
}
