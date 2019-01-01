<?php

namespace drsdre\HelloCash\Test\Functional;

use drsdre\HelloCash\Requests\Invoice;
use drsdre\HelloCash\Test\TestCase;

class InvoiceTest extends TestCase
{
    /**
     * @test
     * @group functional
     */
    public function it_creates_an_order()
    {
        // POST

        $orderCode = app(Invoice::class)->create(1500, [
            'CustomerTrns' => 'Test Transaction',
            'SourceCode' => env('VIVA_SOURCE_CODE'),
            'AllowRecurring' => true,
        ]);

        $this->assertInternalType('integer', $orderCode);

        return $orderCode;
    }

    /**
     * @test
     * @group functional
     * @depends it_creates_an_order
     */
    public function it_gets_an_order($orderCode)
    {
        $response = app(Invoice::class)->get($orderCode);

        $this->assertAttributeEquals(Invoice::PENDING, 'StateId', $response);
        $this->assertAttributeEquals(15, 'RequestAmount', $response);
        $this->assertAttributeEquals('Test Transaction', 'CustomerTrns', $response);
    }

    /**
     * @test
     * @group functional
     * @depends it_creates_an_order
     */
    public function it_updates_an_order($orderCode)
    {
        app(Invoice::class)->update($orderCode, [ 'Amount' => 50]);

        $response = app(Invoice::class)->get($orderCode);

        $this->assertAttributeEquals(0.5, 'RequestAmount', $response);
    }

    /**
     * @test
     * @group functional
     * @depends it_creates_an_order
     */
    public function it_cancels_an_order($orderCode)
    {
        app(Invoice::class)->cancel($orderCode);

        $response = app(Invoice::class)->get($orderCode);

        $this->assertAttributeEquals(Invoice::CANCELED, 'StateId', $response);
    }
}
