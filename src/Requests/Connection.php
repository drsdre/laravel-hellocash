<?php

namespace drsdre\HelloCash\Requests;

use drsdre\HelloCash\HelloCashClient;

class Connection extends BaseRequest
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
     * Get a webhook authorization code.
     *
     * @return object response
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \drsdre\HelloCash\Exceptions\HelloCashException
     */
    final public function getAuthorizationCode(): object
    {
        return $this->client->get(self::ENDPOINT);
    }
}
