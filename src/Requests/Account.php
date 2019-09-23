<?php

namespace drsdre\HelloCash\Requests;

use drsdre\HelloCash\HelloCashClient;
use Illuminate\Support\Carbon;

class Account extends BaseRequest
{
    const ENDPOINT = '/accounts/';

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
