<?php

namespace drsdre\HelloCash\Test\Unit;

use DateTime;
use drsdre\HelloCash\Requests\Transfer;
use drsdre\HelloCash\Test\TestCase;
use Illuminate\Support\Carbon;

class TransferTest extends TestCase
{
    /**
     * @test
     * @group unit
     */
    public function it_creates_a_transaction()
    {
        $this->mockJsonResponses([['foo' => 'bar']]);
        $this->mockRequests();

        $transaction = new Transfer($this->client);

        $parameters = [
            'OrderCode'     => 175936509216,
            'SourceCode'    => 'Default',
            'Installments'  => 36,
            'CreditCard'    => [
                'Token'     => 'foo',
            ],
        ];

        $response = $transaction->create($parameters);
        $request = $this->getLastRequest();

        $this->assertMethod('POST', $request);
        $this->assertBody('OrderCode', '175936509216', $request);
        $this->assertBody('SourceCode', 'Default', $request);
        $this->assertBody('Installments', '36', $request);
        $this->assertBody('CreditCard', ['Token' => 'foo'], $request);
        $this->assertEquals(['foo' => 'bar'], (array) $response);
    }

    /**
     * @test
     * @group unit
     */
    public function it_cancels_a_transaction()
    {
        $this->mockJsonResponses([['foo' => 'bar']]);
        $this->mockRequests();

        $transaction = new Transfer($this->client);

        $response = $transaction->cancel('252b950e-27f2-4300-ada1-4dedd7c17904', 30, 'Customer name');
        $request = $this->getLastRequest();

        $this->assertMethod('DELETE', $request);
        $this->assertPath('/api/transactions/252b950e-27f2-4300-ada1-4dedd7c17904', $request);
        $this->assertQuery('Amount', '30', $request);
        $this->assertQuery('ActionUser', 'Customer name', $request);
        $this->assertEquals(['foo' => 'bar'], (array) $response);
    }

    /**
     * @test
     * @group unit
     */
    public function it_gets_transactions_by_id()
    {
        $this->mockJsonResponses([
            ['Transactions' => [
                ['foo' => 'bar'],
            ]],
        ]);
        $this->mockRequests();

        $transaction = new Transfer($this->client);

        $transactions = $transaction->get('252b950e-27f2-4300-ada1-4dedd7c17904');
        $request = $this->getLastRequest();

        $this->assertMethod('GET', $request);
        $this->assertPath('/api/transactions/252b950e-27f2-4300-ada1-4dedd7c17904', $request);
        $this->assertEquals([(object) ['foo' => 'bar']], $transactions);
    }

    public function dates() : array
    {
        return [
            'string' => ['2016-03-12'],
            DateTime::class => [new DateTime('2016-03-12')],
            Carbon::class => [new Carbon('2016-03-12')],
        ];
    }
}
