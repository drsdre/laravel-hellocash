<?php

namespace drsdre\HelloCash;

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

		$this->app->singleton( HelloCashClient::class, function () {
			return new HelloCashClient();
		} );
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
