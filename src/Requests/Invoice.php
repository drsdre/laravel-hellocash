<?php

namespace drsdre\HelloCash\Requests;

use DateTime;
use drsdre\HelloCash\HelloCashClient;
use GuzzleHttp\RequestOptions;

class Invoice {

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
	 * @var HelloCashClient
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param HelloCashClient $client
	 */
	public function __construct( HelloCashClient $client ) {
		$this->client = $client;
	}

	/**
	 * Validate an invoice.
	 *
	 * @param int $amount
	 * @param string $description
	 * @param string $from_hellocash_account
	 * @param string $tracenumber
	 * @param DateTime $expiration_dt
	 * @param bool $notify_from
	 * @param bool $notify_to
	 * @param array $parameters optional parameters (Full list available here:
	 *                          https://github.com/HelloCash/API/wiki/Optional-Parameters)
	 *
	 * @return object response
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	final public function validate(
		int $amount,
		string $description,
		string $from_hellocash_account,
		string $tracenumber,
		DateTime $expiration_dt,
		bool $notify_from = true,
		bool $notify_to = true,
		array $parameters = []
	): object {
		return $this->client->post( self::ENDPOINT . 'validate',
			array_merge(
				[
					'amount'      => $amount, // Make sure this become numeric
					'description' => $description,
					'from'        => $from_hellocash_account,// Make sure this does not become numeric
					'currency'    => self::CURRENCY,
					'tracenumber' => $tracenumber,
					'expires'     => $expiration_dt->format( DateTime::RFC3339 ),
					'notifyfrom'  => $notify_from,
					'notifyto'    => $notify_to,
				],
				$parameters
			)
		);
	}

	/**
	 * Create an invoice.
	 *
	 * @param int $amount
	 * @param string $description
	 * @param string $from_hellocash_account
	 * @param string $tracenumber
	 * @param DateTime $expiration_dt
	 * @param bool $notify_from
	 * @param bool $notify_to
	 * @param array $parameters optional parameters (Full list available here:
	 *                          https://github.com/HelloCash/API/wiki/Optional-Parameters)
	 *
	 * @return object response
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	final public function create(
		int $amount,
		string $description,
		string $from_hellocash_account,
		string $tracenumber,
		DateTime $expiration_dt,
		bool $notify_from = true,
		bool $notify_to = true,
		array $parameters = []
	): object {
		$parameters = array_merge(
			[
				'amount'      => $amount, // Make sure this become numeric
				'description' => $description,
				'from'        => $from_hellocash_account,// Make sure this does not become numeric
				'currency'    => self::CURRENCY,
				'tracenumber' => $tracenumber,
				'expires'     => $expiration_dt->format( DateTime::RFC3339 ),
				'notifyfrom'  => $notify_from,
				'notifyto'    => $notify_to,
			],
			$parameters
		);

		return $this->client->post( self::ENDPOINT, $parameters );
	}

	/**
	 * Retrieve information about an invoice.
	 *
	 * @param int $invoice_id The unique Invoice ID.
	 *
	 * @return object response
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	final public function get( int $invoice_id ): object {
		return $this->client->get( self::ENDPOINT . $invoice_id );
	}

	/**
	 * List invoices
	 *
	 * @param array $query_params (keys: offset, limit, status, statusdetail, tracenumber, startdate, enddate, descending, from)
	 *
	 * @return object response
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	final public function search( array $query_params ): object {
		return $this->client->get( self::ENDPOINT, $query_params );
	}

	/**
	 * Remove an invoice.
	 *
	 * @param int $invoice_code The unique Invoice ID.
	 *
	 * @return object response
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	final public function remove( int $invoice_code ): object {
		return $this->client->delete( self::ENDPOINT . $invoice_code );
	}
}
