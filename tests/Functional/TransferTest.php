<?php

namespace drsdre\HelloCash\Test\Functional;

use drsdre\HelloCash\Requests\Account;
use drsdre\HelloCash\Requests\Invoice;
use drsdre\HelloCash\Requests\Transfer;
use drsdre\HelloCash\Test\TestCase;
use Illuminate\Support\Carbon;

class TransferTest extends TestCase
{
    /**
     * @test
     * @group functional
     */
    public function createTransfer()
    {
        $orderCode = $this->getOrderCode();
        $token = $this->getToken();
        $installments = $this->getInstallments();

        $original = app(Transfer::class)->create([
            'OrderCode'       => $orderCode,
            'SourceCode'      => env('VIVA_SOURCE_CODE'),
            'Installments'    => $installments,
            'AllowsRecurring' => true,
            'CreditCard'      => [
                'Token'       => $token,
            ],
        ]);

        $this->assertAttributeEquals(Transfer::COMPLETED, 'StatusId', $original, 'The transaction was not completed.');
        $this->assertAttributeEquals(15, 'Amount', $original);

        return $original;
    }

    /**
     * @test
     * @group functional
     * @depends createTransaction
     */
    public function getById($original)
    {
        $transactions = app(Transfer::class)->get($original->TransactionId);

        $this->assertNotEmpty($transactions);
        $this->assertCount(1, $transactions, 'There should be 1 transaction.');
        $this->assertAttributeEquals(Transfer::COMPLETED, 'StatusId', $transactions[0], 'The transaction was not completed.');
        $this->assertAttributeEquals($original->TransactionId, 'TransactionId', $transactions[0], "The transaction ID should be {$original->TransactionId}.");

        return $transactions[0];
    }

    /**
     * @test
     * @group functional
     * @depends createTransaction
     */
    public function cancelTransaction($original)
    {
        $transaction = app(Transfer::class);

        $response = $transaction->cancel($original->TransactionId, 1500);

        $this->assertAttributeEquals(Transfer::COMPLETED, 'StatusId', $response, 'The cancel transaction was not completed.');
        $this->assertAttributeEquals(15, 'Amount', $response);

        $transactions = $transaction->get($original->TransactionId);

        $this->assertNotEmpty($transactions);
        $this->assertCount(1, $transactions, 'There should be 1 transaction.');
        $this->assertAttributeEquals(Transfer::CANCELED, 'StatusId', $transactions[0], 'The original transaction should be canceled.');
        $this->assertAttributeEquals(15, 'Amount', $transactions[0]);
    }

    protected function getOrderCode()
    {
        return app(Invoice::class)->create(1500, [
            'CustomerTrns' => 'Test Transaction',
            'SourceCode' => env('VIVA_SOURCE_CODE'),
            'AllowRecurring' => true,
        ]);
    }

    protected function getToken()
    {
        $expirationDate = Carbon::parse('next year');

        return app(Account::class)->token('Customer name', '4111 1111 1111 1111', 111, $expirationDate->month, $expirationDate->year);
    }

    protected function getInstallments()
    {
        return app(Account::class)->installments('4111 1111 1111 1111');
    }
}
