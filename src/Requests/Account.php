<?php

namespace drsdre\HelloCash\Requests;

use drsdre\HelloCash\HelloCashClient;
use Illuminate\Support\Carbon;

class Account
{
    const ENDPOINT = '/accounts/';

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

    /**
     * Get the public key as query string.
     *
     * @return string
     */
    protected function getSystem() : string
    {
        return config('service.hellocash.system');
    }

    /**
     * Get the expiration date.
     *
     * @param  int $month
     * @param  int $year
     * @return string
     */
    protected function getExpirationDate(int $month, int $year) : string
    {
        return Carbon::createFromDate($year, $month, 15)->toDateString();
    }
}
