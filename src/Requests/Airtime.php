<?php

namespace drsdre\HelloCash\Requests;

use DateTime;
use drsdre\HelloCash\HelloCashClient;

class Airtime {

	const ENDPOINT = '/airtime/';

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
	 * Finds a list of airtime transfers to and from this account.
	 *
	 * @param int $offset
	 * @param int $limit
	 * @param array $status
	 * @param string $statusdetail
	 * @param string $tracenumber
	 * @param string $startdate
	 * @param string $enddate
	 * @param bool $descending
	 *
	 * @return object response
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	public function get(
		int $offset,
		int $limit,
		array $status,
		string $statusdetail,
		string $tracenumber,
		string $startdate,
		string $enddate,
		bool $descending
	) {
		return $this->client->request( 'GET', self::ENDPOINT, [
			'offset'       => $offset,
			'limit'        => $limit,
			'status'       => $status,
			'statusdetail' => $statusdetail,
			'tracenumber'  => $tracenumber,
			'startdate'    => $startdate,
			'enddate'      => $enddate,
			'descending'   => $descending,
		] );
	}

	/**
	 * Create a new airtime transfer
	 *
	 * @param int $amount
	 * @param string $description
	 * @param string $from_hellocash_account
	 * @param string $tracenumber
	 * @param string $expiration_date
	 * @param bool $notify_from
	 * @param bool $notify_to
	 * @param bool $validate_only
	 * @param array $parameters
	 *
	 * @return object response
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	public function create(
		int $amount,
		string $description,
		string $from_hellocash_account,
		string $tracenumber,
		string $expiration_date,
		bool $notify_from = true,
		bool $notify_to = true,
		bool $validate_only = false,
		array $parameters = []
	): object {
		$ExpireDatetime = DateTime::createFromFormat( "Y-m-d H:i", $expiration_date );

		return $this->client->post( self::ENDPOINT . ( $validate_only ? 'validate' : '' ),
			array_merge(
				[
					'amount'      => $amount, // Make sure this become numeric
					'description' => $description,
					'from'        => $from_hellocash_account,// Make sure this does not become numeric
					'currency'    => 'ETB',
					'tracenumber' => $tracenumber,
					'expires'     => $ExpireDatetime->format( DateTime::RFC3339 ),
					'notifyfrom'  => $notify_from,
					'notifyto'    => $notify_to,
				],
				$parameters
			)
		);
	}

	/**
	 * List available airtime topup amounts
	 *
	 * @return object response
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \drsdre\HelloCash\Exceptions\HelloCashException
	 */
	final public function available(): object {
		return $this->client->get( self::ENDPOINT . 'available' );
	}

	/**
	 * Get the public key as query string.
	 *
	 * @return string
	 */
	final protected function getSystem(): string {
		return config( 'service.hellocash.system' );
	}
}
