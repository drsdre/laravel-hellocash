<?php

namespace drsdre\HelloCash\Requests;

use drsdre\HelloCash\HelloCashClient;

class Transfer extends BaseRequest {

	const ENDPOINT = '/transfers/';

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

    /**
     * Create a transfer.
     *
     * @param int $amount
     * @param string $currency
     * @param string $description
     * @param string $to_hellocash_account
     * @param string $tracenumber
     * @param string $referenceid
     * @param bool $notify_from
     * @param bool $notify_to
     * @param array $parameters optional parameters (Full list available here:
     *                          https://github.com/HelloCash/API/wiki/Optional-Parameters)
     * @param bool $replace Replace existing transfer with referenceid with new transfer
     * @param bool $validate_only Only validate data, don't create transfer
     *
     * @return object response
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \drsdre\HelloCash\Exceptions\HelloCashException
     */
    final public function create(
        int $amount,
        string $currency,
        string $description,
        string $to_hellocash_account,
        string $tracenumber,
        string $referenceid,
        bool $notify_from = true,
        bool $notify_to = true,
        array $parameters = [],
        bool $replace = false,
        bool $validate_only = false
    ): object {
        $parameters = array_merge(
            [
                'amount'      => $amount, // Make sure this become numeric
                'description' => $description,
                'to'          => $to_hellocash_account,// Make sure this does not become numeric
                'currency'    => $currency,
                'tracenumber' => $tracenumber,
                'referenceid' => $referenceid,
                'notifyfrom'  => $notify_from,
                'notifyto'    => $notify_to,
            ],
            $parameters
        );

        return $replace
            ? $this->client->put( self::ENDPOINT . $referenceid, $parameters )
            : $this->client->post( self::ENDPOINT . ( $validate_only ? 'validate' : ''), $parameters );
    }

	/**
	 * Get the transactions for an id.
	 *
	 * @param string $transfer_id
	 *
	 * @return array transactions
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	final public function get( string $transfer_id ): object {
		$response = $this->client->get( self::ENDPOINT . $transfer_id );

		return $response;
	}


	/**
	 * Search for transfers.
	 *
	 * @param array $query_params (keys: offset, limit, status, statusdetail, tracenumber, referenceid, startdate, enddate)
	 *
	 * @return array transactions
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	final public function search( array $query_params ): object {
		$response = $this->client->get( self::ENDPOINT, $query_params );

		return $response->Transactions;
	}

	/**
	 * Cancel a list of transfers.
	 *
	 * @param array $transfer_ids
	 *
	 * @return bool true if deleted
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	final public function cancel( array $transfer_ids ): bool {
		$this->client->delete( self::ENDPOINT . 'cancel', [ 'TransferIdList' => $transfer_ids ] );
        return true;
	}

	/**
	 * Authorize a list of transfers
	 *
	 * @param array $transfer_ids
	 *
	 * @return object response
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	final public function authorize( array $transfer_ids ): object {
		return $this->client->post( self::ENDPOINT . 'authorize', [ 'TransferIdList' => $transfer_ids ] );
	}
}
