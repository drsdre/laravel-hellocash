<?php

namespace drsdre\HelloCash\Test\Unit;

use drsdre\HelloCash\Requests\Account;
use drsdre\HelloCash\Test\TestCase;

class AccountTest extends TestCase
{
    /**
     * @test
     * @group unit
     */
    public function it_creates_a_token()
    {
        $this->mockJsonResponses([['Token' => 'foo']]);
        $this->mockRequests();

        $card = new Account($this->client);

        $token = $card->token('Customer name', '4111 1111 1111 1111', 111, 06, 2016);
        $request = $this->getLastRequest();

        $this->assertInternalType('string', $token);
        $this->assertEquals('foo', $token, 'The token should be foo');
        $this->assertMethod('POST', $request);
        $this->assertBody('CardHolderName', 'Customer name', $request);
        $this->assertBody('Number', '4111111111111111', $request);
        $this->assertBody('CVC', '111', $request);
        $this->assertBody('ExpirationDate', '2016-06-15', $request);
    }

    /**
     * @test
     * @group unit
     */
    public function it_checks_for_installments()
    {
        $this->mockJsonResponses([['MaxInstallments' => 36]]);
        $this->mockRequests();

        $card = new Account($this->client);

        $installments = $card->installments('4111 1111 1111 1111');
        $request = $this->getLastRequest();

        $this->assertMethod('GET', $request);
        $this->assertHeader('CardNumber', 4111111111111111, $request);
        $this->assertEquals(36, $installments, 'The installments should be 36.');
    }
}
