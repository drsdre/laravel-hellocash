<?php

namespace drsdre\HelloCash;

use Closure;
use drsdre\HelloCash\Exceptions\HelloCashException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cache;
use Log;
use Psr\SimpleCache\InvalidArgumentException;

class HelloCashClient
{

    /**
     * Production environment URL.
     */
    const PRODUCTION_URL = 'https://api-et.hellocash.net';

    const TOKEN_CACHE_KEY = 'hellocash_token';

    const TOKEN_CACHE_HOURS = 23;

    const STATUS_DRAFT = 'Pending sending to HelloCash';
    const STATUS_SYSTEM_ERROR = 'HelloCash system error';
    const STATUS_DATA_ERROR = 'Data error creating invoice';
    const STATUS_NETWORK_ERROR = 'HelloCash down (API)';

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var object
     */
    public $response;

    /**
     * @var object
     */
    protected $response_body;

    /**
     * @var int
     */
    protected $token_retries = 0;

    /**
     * HelloCashClient constructor.
     *
     * @param GuzzleClient $client
     */
    public function __construct(GuzzleClient $client)
    {
        $this->client = new $client([
            'base_uri' => $this->getUrl(),
            'curl'     => $this->curlDoesntUseNss()
                ? [ CURLOPT_SSL_CIPHER_LIST => 'TLSv1' ]
                : [],
        ]);
    }

    /**
     * Get the URL based on the environment.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return self::PRODUCTION_URL;
    }

    /**
     * Check if cURL doens't use NSS.
     *
     * @return bool
     */
    final private function curlDoesntUseNss(): bool
    {
        $curl = curl_version();

        return ! preg_match('/NSS/', $curl['ssl_version']);
    }

    /**
     * Get the Guzzle client.
     *
     * @return GuzzleClient
     */
    final public function getClient(): GuzzleClient
    {
        return $this->client;
    }

    /**
     * @return array
     * @throws HelloCashException
     * @throws InvalidArgumentException
     */
    final private function authenticateHeader(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getBearerToken(),
        ];
    }

    /**
     * Load response into class
     *
     * @param Closure $call_api
     *
     * @return object
     * @throws HelloCashException
     */
    final private function loadResponse(Closure $call_api): object
    {
        try {
            do {
                $this->response = $call_api();
                $this->response_body = json_decode($this->response->getBody(), false, 512, JSON_BIGINT_AS_STRING);
            } while ($this->testForErrorAndInvalidTokenRetry());

            return $this->response_body;
        } catch (ServerException $e) {
            $message = 'HelloCash Server Error: ' . $e->getMessage();

            // Retry
            throw new HelloCashException($message, HelloCashException::SERVER_ERROR, true);
        } catch (ClientException $e) {
            if ($e->getCode() == 403) {
                // If not, set status to error
                $message = 'HelloCash Credentials are wrong, please reconfigure. '  . $e->getMessage();

                Log::error($message);

                // Don't retry
                throw new HelloCashException($message, HelloCashException::WRONG_CREDENTIALS, false);
            }

            $this->response_body = json_decode($e->getResponse()->getBody(), false);
            if (isset($this->response_body->error->message) && $this->response_body->error->message == 'FROM_INVALID') {
                $message = 'Invalid HelloCash account:' . $e->getMessage();
            } else {
                // Bad response exception, handle this error
                $message = 'Data Error: ' . $e->getMessage() . ' ' .
                           (isset($this->response_body->error->message) ?
                               $this->response_body->error->message :
                               print_r($this->response_body, true));
            }

            // Don't retry
            throw new HelloCashException($message, HelloCashException::CLIENT_EXCEPTION, false);
        } catch (RequestException $e) {
            // Retry
            throw new HelloCashException('Unable to contact HelloCash: ' . $e->getMessage(), HelloCashException::NETWORK_UNAVAILABLE, true);
        }
    }

    /**
     * Test if response has error and throws HelloCashException if so
     * or if INVALID_TOKEN, reset token and retry.
     *
     * @return bool
     * @throws HelloCashException
     */
    final private function testForErrorAndInvalidTokenRetry(): bool
    {
        if (isset($this->response->ErrorCode) && $this->response->ErrorCode !== 0) {
            if (
                isset($this->response_body->error->message) &&
                $this->response_body->error->message == 'INVALID_TOKEN' &&
                // Retry once
                $this->token_retries == 0
            ) {
                // Delete token
                Cache::delete(self::TOKEN_CACHE_KEY);
                $this->token_retries++;
                // Retry
                return true;
            } else {
                throw new HelloCashException($this->response->ErrorText, $this->response->ErrorCode, true);
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
    final private function getBearerToken(): string
    {
        if (Cache::has(self::TOKEN_CACHE_KEY)) {
            return Cache::get(self::TOKEN_CACHE_KEY);
        }

        $this->response_body = $this->loadResponse(function () {
            $config = config('hellocash');
            return $this->client->post('/authenticate', [
                RequestOptions::JSON => [
                    'principal'   => $config['principal'],
                    'credentials' => $config['credentials'],
                    'system'      => $config['system'],
                ],
            ]);
        });

        // Cache token for 23 hours (according to HelloCash documentation
        Cache::put(self::TOKEN_CACHE_KEY, $this->response_body->token, now()->addHours(self::TOKEN_CACHE_HOURS));

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
     */
    public function get(string $url, array $query = []): object
    {
        return $this->loadResponse(function () use ($url, $query) {
            return $this->client->request('GET', $url, [
                RequestOptions::QUERY   => $query,
                RequestOptions::HEADERS => $this->authenticateHeader(),
            ]);
        });
    }

    /**
     * Make a POST request.
     *
     * @param string $url
     * @param array $options
     *
     * @return object response body
     * @throws HelloCashException
     */
    public function post(string $url, array $options = []): object
    {
        return $this->loadResponse(function () use ($url, $options) {
            return $this->client->request('POST', $url, [
                RequestOptions::JSON    => $options,
                RequestOptions::HEADERS => $this->authenticateHeader(),
            ]);
        });
    }

    /**
     * Make a PATCH request.
     *
     * @param string $url
     * @param array $query
     * @param array $options
     *
     * @return object response body
     * @throws HelloCashException
     */
    public function put(string $url, array $query = [], array $options = []): object
    {
        return $this->loadResponse(function () use ($url, $query, $options) {
            return $this->client->request('PUT', $url, [
                RequestOptions::QUERY   => $query,
                RequestOptions::JSON    => $options,
                RequestOptions::HEADERS => $this->authenticateHeader(),
            ]);
        });
    }

    /**
     * Make a DELETE request.
     *
     * @param string $url
     * @param array $query
     *
     * @return object response body
     * @throws HelloCashException
     */
    public function delete(string $url, array $query = []): object
    {
        return $this->loadResponse(function () use ($url, $query) {
            return $this->client->request('DELETE', $url, [
                RequestOptions::QUERY   => $query,
                RequestOptions::HEADERS => $this->authenticateHeader(),
            ]);
        });
    }
}
