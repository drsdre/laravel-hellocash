<?php

namespace drsdre\HelloCash\Requests;

use drsdre\HelloCash\HelloCashClient;

class Connection
{
    const ENDPOINT = '/connections/';

    /**
     * Create Transaction event.
     */
    const CREATE_TRANSFER = 1796;

    /**
     * Cancel/Refund Transaction event.
     */
    const REFUND_TRANSACTION = 1797;

    /**
     * @var HelloCashClient
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param HelloCashClient $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get a webhook authorization code.
     *
     * @return object
     */
    public function getAuthorizationCode()
    {
        return $this->client->get(self::ENDPOINT);
    }
}
