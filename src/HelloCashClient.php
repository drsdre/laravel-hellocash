<?php

namespace drsdre\HelloCash;

use drsdre\HelloCash\Exceptions\HelloCashException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface;

class HelloCashClient {

	/**
	 * Production environment URL.
	 */
	const PRODUCTION_URL = 'https://api-et.hellocash.net';

	const TOKEN_CACHE_KEY = 'hellocash_token';

	const TOKEN_CACHE_HOURS = 23;

	/**
	 * @var GuzzleClient
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param GuzzleClient $client
	 *
	 * @return void
	 */
	public function __construct() {
		$this->client = new GuzzleClient( [
			'base_uri' => $this->getUrl(),
			'curl'     => $this->curlDoesntUseNss()
				? [ CURLOPT_SSL_CIPHER_LIST => 'TLSv1' ]
				: [],
		] );
	}

	/**
	 * Get the URL based on the environment.
	 *
	 * @return string
	 */
	final public function getUrl(): string {
		return self::PRODUCTION_URL;
	}

	/**
	 * Check if cURL doens't use NSS.
	 *
	 * @return bool
	 */
	final private function curlDoesntUseNss(): bool {
		$curl = curl_version();

		return ! preg_match( '/NSS/', $curl['ssl_version'] );
	}

	/**
	 * Get the Guzzle client.
	 *
	 * @return GuzzleClient
	 */
	final public function getClient(): GuzzleClient {
		return $this->client;
	}

	/**
	 * @return array
	 * @throws HelloCashException
	 */
	final private function authenticateHeader(): array {
		return [
			'Authorization' => 'Bearer ' . $this->getBearerToken(),
		];
	}

	/**
	 * Get the response body.
	 *
	 * @param ResponseInterface $response
	 *
	 * @return object
	 *
	 * @throws HelloCashException
	 */
	final private function getBody( ResponseInterface $response ): object {
		$body = json_decode( $response->getBody(), false, 512, JSON_BIGINT_AS_STRING );

		if ( isset( $body->ErrorCode ) && $body->ErrorCode !== 0 ) {
			throw new HelloCashException( $body->ErrorText, $body->ErrorCode );
		}

		return $body;
	}

	/**
	 * Get the bearer authentication token
	 *
	 * @return string token
	 * @throws HelloCashException
	 */
	final private function getBearerToken(): string {
		if ( Cache::has( self::TOKEN_CACHE_KEY ) ) {
			return Cache::get( self::TOKEN_CACHE_KEY );
		}

		$config = config( 'hellocash' );

		$client = new GuzzleClient( [
			'base_uri' => $this->getUrl(),
			'curl'     => $this->curlDoesntUseNss()
				? [ CURLOPT_SSL_CIPHER_LIST => 'TLSv1' ]
				: [],
		] );

		$response = $client->post( '/authenticate', [
			RequestOptions::JSON => [
				'principal'   => $config['principal'],
				'credentials' => $config['credentials'],
				'system'      => $config['system'],
			],
		] );

		$data = $this->getBody( $response );

		// Cache token for 23 hours (according to HelloCash documentation
		Cache::put( self::TOKEN_CACHE_KEY, $data->token, now()->addHours( self::TOKEN_CACHE_HOURS ) );

		return $data->token;
	}

	/**
	 * Make a GET request.
	 *
	 * @param string $url
	 * @param array $query
	 *
	 * @return object
	 * @throws HelloCashException
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	final public function get( string $url, array $query = [] ): object {
		$response = $this->client->request( 'GET', $url, [
			RequestOptions::QUERY   => $query,
			RequestOptions::HEADERS => $this->authenticateHeader(),
		] );

		return $this->getBody( $response );
	}

	/**
	 * Make a POST request.
	 *
	 * @param string $url
	 * @param array $options
	 *
	 * @return object
	 * @throws HelloCashException
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	final public function post( string $url, array $options = [] ): object {
		$response = $this->client->request( 'POST', $url, [
			RequestOptions::JSON    => $options,
			RequestOptions::HEADERS => $this->authenticateHeader(),
		] );

		return $this->getBody( $response );
	}

	/**
	 * Make a PATCH request.
	 *
	 * @param string $url
	 * @param array $options
	 *
	 * @return object
	 * @throws HelloCashException
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	final public function put( string $url, array $query = [], array $options = [] ): object {
		$response = $this->client->request( 'PUT', $url, [
			RequestOptions::QUERY   => $query,
			RequestOptions::JSON    => $options,
			RequestOptions::HEADERS => $this->authenticateHeader(),
		] );

		return $this->getBody( $response );
	}

	/**
	 * Make a DELETE request.
	 *
	 * @param string $url
	 * @param array $query
	 *
	 * @return object
	 * @throws HelloCashException
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	final public function delete( string $url, array $query = [] ): object {
		$response = $this->client->request( 'DELETE', $url, [
			RequestOptions::QUERY   => $query,
			RequestOptions::HEADERS => $this->authenticateHeader(),
		] );

		return $this->getBody( $response );
	}
}
