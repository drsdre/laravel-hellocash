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
	 * @var object
	 */
	protected $response;

	/**
	 * @var int
	 */
	protected $token_retries = 0;

	/**
	 * @var object
	 */
	public $response_body;

	/**
	 * Constructor.
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
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	final private function authenticateHeader(): array {
		return [
			'Authorization' => 'Bearer ' . $this->getBearerToken(),
		];
	}

	/**
	 * Load response into class
	 *
	 * @param ResponseInterface $response
	 */
	final private function loadResponse( ResponseInterface $response ): void {
		$this->response = $response;
		$this->response_body = json_decode( $response->getBody(), false, 512, JSON_BIGINT_AS_STRING );
	}

	/**
	 * Test if response has error and throws HelloCashException if so
	 * or if INVALID_TOKEN, reset token and retry.
	 *
	 * @return bool
	 * @throws HelloCashException
	 */
	final private function testForErrorAndInvalidTokenRetry(): bool {
		if ( isset( $this->response->ErrorCode ) && $this->response->ErrorCode !== 0 ) {
			if (
				isset( $this->response_body->error->message ) &&
				$this->response_body->error->message == 'INVALID_TOKEN' &&
				// Retry once
				$this->token_retries == 0
			) {
				// Delete token
				Cache::delete( self::TOKEN_CACHE_KEY );
				$this->token_retries++;
				// Retry
				return true;
			} else {
				throw new HelloCashException( $this->response->ErrorText, $this->response->ErrorCode );
			}
		}

		// Don't retry
		return false;
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

		$this->loadResponse(
			$client->post( '/authenticate', [
				RequestOptions::JSON => [
					'principal'   => $config['principal'],
					'credentials' => $config['credentials'],
					'system'      => $config['system'],
				],
			] )
		);

		$this->testForErrorAndInvalidTokenRetry();

		// Cache token for 23 hours (according to HelloCash documentation
		Cache::put( self::TOKEN_CACHE_KEY, $this->response_body->token, now()->addHours( self::TOKEN_CACHE_HOURS ) );

		return $this->response_body->token;
	}

	/**
	 * Make a GET request.
	 *
	 * @param string $url
	 * @param array $query
	 *
	 * @return object response body
	 * @throws HelloCashException
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	final public function get( string $url, array $query = [] ): object {
		do {
			$this->loadResponse(
				$this->client->request( 'GET', $url, [
					RequestOptions::QUERY   => $query,
					RequestOptions::HEADERS => $this->authenticateHeader(),
				] )
			);
		} while ( $this->testForErrorAndInvalidTokenRetry() );

		return $this->response_body;
	}

	/**
	 * Make a POST request.
	 *
	 * @param string $url
	 * @param array $options
	 *
	 * @return object response body
	 * @throws HelloCashException
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	final public function post( string $url, array $options = [] ): object {
		do {
			$this->loadResponse(
				$this->client->request( 'POST', $url, [
					RequestOptions::JSON    => $options,
					RequestOptions::HEADERS => $this->authenticateHeader(),
				] )
			);
		} while ( $this->testForErrorAndInvalidTokenRetry() );

		return $this->response_body;
	}

	/**
	 * Make a PATCH request.
	 *
	 * @param string $url
	 * @param array $options
	 *
	 * @return object response body
	 * @throws HelloCashException
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	final public function put( string $url, array $query = [], array $options = [] ): object {
		do {
			$this->loadResponse(
				$this->client->request( 'PUT', $url, [
					RequestOptions::QUERY   => $query,
					RequestOptions::JSON    => $options,
					RequestOptions::HEADERS => $this->authenticateHeader(),
				] )
			);
		} while ( $this->testForErrorAndInvalidTokenRetry() );

		return $this->response_body;
	}

	/**
	 * Make a DELETE request.
	 *
	 * @param string $url
	 * @param array $query
	 *
	 * @return object response body
	 * @throws HelloCashException
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	final public function delete( string $url, array $query = [] ): object {
		do {
			$this->loadResponse(
				$this->client->request( 'DELETE', $url, [
					RequestOptions::QUERY   => $query,
					RequestOptions::HEADERS => $this->authenticateHeader(),
				] )
			);
		} while ( $this->testForErrorAndInvalidTokenRetry() );

		return $this->response_body;
	}
}
