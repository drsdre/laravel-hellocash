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
	 * Create a new transaction.
	 *
	 * @param array $transfer_parameters
	 *
	 * @return object response
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	final public function validate( array $transfer_parameters ): object {
		return $this->client->post(
			self::ENDPOINT . 'validate',
			$transfer_parameters
		);
	}

	/**
	 * Create a new transaction.
	 *
	 * @param array $transfer_parameters
	 *
	 * @return object response
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	final public function create( array $transfer_parameters ): object {
		return $this->client->post(
			self::ENDPOINT,
			$transfer_parameters
		);
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
	final public function get( string $transfer_id ): array {
		$response = $this->client->get( self::ENDPOINT . $transfer_id );

		return $response->Transactions;
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
	final public function search( array $query_params ): array {
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
