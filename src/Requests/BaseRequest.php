<?php


namespace drsdre\HelloCash\Requests;

use drsdre\HelloCash\HelloCashClient;

class BaseRequest
{

    /**
     * @var HelloCashClient
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param HelloCashClient $client
     */
    public function __construct(HelloCashClient $client)
    {
        $this->client = $client;
    }

    final public function getClientResponse(): object
    {
        return $this->client->response_body;
    }
}
