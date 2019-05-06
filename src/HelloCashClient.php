<?php

namespace drsdre\HelloCash;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class HelloCashClient
{
    /**
     * Production environment URL.
     */
    const PRODUCTION_URL = 'https://api-et.hellocash.net';

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
    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    /**
     * Make a GET request.
     *
     * @param  string $url
     * @param  array  $options
     * @return object
     */
    public function get(string $url, array $options = [])
    {
        $response = $this->client->get($url, $options);

        return $this->getBody($response);
    }

    /**
     * Make a POST request.
     *
     * @param  string $url
     * @param  array  $options
     * @return object
     */
    public function post(string $url, array $options = [])
    {
        $response = $this->client->post($url, $options);

        return $this->getBody($response);
    }

    /**
     * Make a PATCH request.
     *
     * @param  string $url
     * @param  array  $options
     * @return object
     */
    public function patch(string $url, array $options = [])
    {
        $response = $this->client->patch($url, $options);

        return $this->getBody($response);
    }

    /**
     * Make a DELETE request.
     *
     * @param  string $url
     * @param  array  $options
     * @return object
     */
    public function delete(string $url, array $options = [])
    {
        $response = $this->client->delete($url, $options);

        return $this->getBody($response);
    }

    /**
     * Get the response body.
     *
     * @param ResponseInterface $response
     * @return object
     *
     * @throws HelloCashException
     */
    protected function getBody(ResponseInterface $response)
    {
        $body = json_decode($response->getBody(), false, 512, JSON_BIGINT_AS_STRING);

        if (isset($body->ErrorCode) && $body->ErrorCode !== 0) {
            throw new HelloCashException($body->ErrorText, $body->ErrorCode);
        }

        return $body;
    }

    /**
     * Get the URL.
     *
     * @return UriInterface
     */
    public function getUrl() : UriInterface
    {
        return $this->client->getConfig('base_uri');
    }

    /**
     * Get the Guzzle client.
     *
     * @return GuzzleClient
     */
    public function getClient()
    {
        return $this->client;
    }
}
