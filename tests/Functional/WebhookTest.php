<?php

namespace drsdre\HelloCash\Test\Functional;

use drsdre\HelloCash\Connection;
use drsdre\HelloCash\Test\TestCase;

class WebhookTest extends TestCase
{
    /**
     * @test
     * @group functional
     */
    public function it_gets_an_authorization_code()
    {
        $code = app(Connection::class)->getAuthorizationCode();

        $this->assertObjectHasAttribute('Key', $code);
        $this->assertInternalType('string', $code->Key);
    }
}
