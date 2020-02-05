<?php

namespace drsdre\HelloCash\Requests;

use DateTime;
use drsdre\HelloCash\Exceptions\HelloCashException;
use GuzzleHttp\Exception\GuzzleException;

class Invoice extends BaseRequest
{
    const ENDPOINT = '/invoices/';

    // Transient statuses
    const STATUS_INITIALIZING = 'INITIALIZING';
    const STATUS_VERIFYING = 'VERIFYING';
    const STATUS_AUTHORIZING = 'AUTHORIZING';

    // Long-term transient statuses
    const STATUS_PREPARED = 'PREPARED';
    const STATUS_PENDING = 'PENDING';
    const STATUS_RECEIVED = 'RECEIVED';

    // Final statuses
    const STATUS_PROCESSED = 'PROCESSED';
    const STATUS_DENIED = 'DENIED';
    const STATUS_CANCELED = 'CANCELED';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_REPLACED = 'REPLACED';

    const CURRENCY = 'ETB';

    /**
     * Create an invoice.
     *
     * @param int $amount
     * @param string $description
     * @param string $from_account
     * @param string $trace_number
     * @param DateTime $expiration_dt
     * @param bool $notify_from
     * @param bool $notify_to
     * @param array $parameters optional parameters (Full list available here:
     *                          https://github.com/HelloCash/API/wiki/Optional-Parameters)
     * @param bool $validate_only Only validate data, don't create invoice
     *
     *
     * @return object response
     *
     * @throws GuzzleException
     * @throws HelloCashException
     */
    final public function create(
        int $amount,
        string $description,
        string $from_account,
        string $trace_number,
        DateTime $expiration_dt,
        bool $notify_from = true,
        bool $notify_to = true,
        array $parameters = [],
        bool $validate_only = false
    ): object {
        $parameters = array_merge(
            [
                'amount'      => $amount, // Make sure this become numeric
                'description' => $description,
                'from'        => $from_account,// Make sure this does not become numeric
                'currency'    => self::CURRENCY,
                'tracenumber' => $trace_number,
                'expires'     => $expiration_dt->format(DateTime::RFC3339),
                'notifyfrom'  => $notify_from,
                'notifyto'    => $notify_to,
            ],
            $parameters
        );

        return $this->client->post(self::ENDPOINT. ($validate_only ? 'validate' : ''), $parameters);
    }

    /**
     * Retrieve information about an invoice.
     *
     * @param string $invoice_id The unique Invoice ID.
     *
     * @return object response
     *
     * @throws GuzzleException
     * @throws HelloCashException
     */
    final public function get(string $invoice_id): object
    {
        return $this->client->get(self::ENDPOINT . $invoice_id);
    }

    /**
     * List invoices
     *
     * @param array $query_params (keys: offset, limit, status, statusdetail, tracenumber, startdate, enddate, descending, from)
     *
     * @return object response
     *
     * @throws GuzzleException
     * @throws HelloCashException
     */
    final public function search(array $query_params): object
    {
        return $this->client->get(self::ENDPOINT, $query_params);
    }

    /**
     * Remove an invoice.
     *
     * @param int $invoice_code The unique Invoice ID.
     *
     * @return bool true if deleted
     *
     * @throws GuzzleException
     * @throws HelloCashException
     */
    final public function remove(int $invoice_code): bool
    {
        $this->client->delete(self::ENDPOINT . $invoice_code);
        return true;
    }
}
