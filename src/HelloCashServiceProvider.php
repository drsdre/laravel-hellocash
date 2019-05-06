<?php

namespace drsdre\HelloCash;

use drsdre\HelloCash\Exceptions\HelloCashException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class HelloCashServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	protected $bearer_token;

	public function boot()
	{
		$configPath = __DIR__.'/../config';
		$this->mergeConfigFrom($configPath.'/config.php', 'hellocash');
		$this->publishes([
			$configPath.'/config.php' => config_path('hellocash.php'),
		], 'config');
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register() {
		$this->mergeConfigFrom(
			__DIR__ . '/../config/config.php',
			'services'
		);

		$this->app->singleton( HelloCashClient::class, function ( $app ) {
			return new HelloCashClient( $this->buildGuzzleClient( $app ) );
		} );
	}

	/**
	 * Build the Guzzlehttp client.
	 *
	 * @param  Application $app
	 **
	 * @return GuzzleClient
	 * @throws HelloCashException
	 */
	protected function buildGuzzleClient( $app ) {

		return new GuzzleClient( [
			'base_uri'                       => $this->getUrl(),
			'curl'                           => $this->curlDoesntUseNss()
				? [ CURLOPT_SSL_CIPHER_LIST => 'TLSv1' ]
				: [],
			'headers' => [
				'Authorization' => 'Bearer ' . $this->bearerToken( $app ),
			],
		] );
	}

	/**
	 * Get the URL based on the environment.
	 *
	 * @return string
	 */
	protected function getUrl() {
		return HelloCashClient::PRODUCTION_URL;
	}

	/**
	 * Check if cURL doens't use NSS.
	 *
	 * @return bool
	 */
	protected function curlDoesntUseNss() {
		$curl = curl_version();

		return ! preg_match( '/NSS/', $curl['ssl_version'] );
	}

	/**
	 * Get the bearer authentication token
	 *
	 * @param $app
	 *
	 * @return string token
	 * @throws HelloCashException
	 */
	private function bearerToken( $app ): string {
		if ( $this->bearer_token ) {
			return $this->bearer_token;
		}

		$config = $app['config']->get( 'hellocash' );

		$client = new GuzzleClient( [
			'base_uri'                       => $this->getUrl(),
			'curl'                           => $this->curlDoesntUseNss()
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

		$data = json_decode( $response->getBody() );

		$this->bearer_token = $data->token;

		return $this->bearer_token;
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		return [ HelloCashClient::class ];
	}
}
