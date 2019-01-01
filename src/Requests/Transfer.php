<?php

namespace drsdre\HelloCash\Requests;

class Transfer
{
    const ENDPOINT = '/transfer/';

	// Transient statuses
	const STATUS_INITIALIZING = 'INITIALIZING';
	const STATUS_VERIFYING = 'VERIFYING';
	const STATUS_AUTHORIZING = 'AUTHORIZING';

	// Long-term transient statuses
	const STATUS_PREPARED = 'PREPARED';
	const STATUS_PENDING = 'PENDING';
	const STATUS_RECEIVED = 'RECEIVED';

	// Final statuses
	const STATUS_PROCESSED = 'PROCESSED';
	const STATUS_DENIED = 'DENIED';
	const STATUS_CANCELED = 'CANCELED';
	const STATUS_EXPIRED = 'EXPIRED';
	const STATUS_REPLACED = 'REPLACED';

    /**
     * @var \drsdre\HelloCash\HelloCashClient
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param \drsdre\HelloCash\HelloCashClient $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new transaction.
     *
     * @param  array   $parameters
     * @return object
     */
    public function create(array $parameters)
    {
        return $this->client->post(self::ENDPOINT, [
            \GuzzleHttp\RequestOptions::FORM_PARAMS => $parameters,
            \GuzzleHttp\RequestOptions::QUERY => [
                'key' => $this->getKey(),
            ],
        ]);
    }

    /**
     * Get the transactions for an id.
     *
     * @param  string $id
     * @return array
     */
    public function get(string $id) : array
    {
        $response = $this->client->get(self::ENDPOINT.$id);

        return $response->Transactions;
    }

    /**
     * Format a date object to string.
     *
     * @param  \DateTimeInterface|string $date
     * @return string
     */
    protected function formatDate($date) : string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        return $date;
    }

	/**
	 * Strip non-numeric characters.
	 *
	 * @param  string $number  The credit card number
	 * @return string
	 */
	protected function normalizeNumber(string $number) : string
	{
		return preg_replace('/\D/', '', $number);
	}

    /**
     * Cancel or refund a payment.
     *
     * @param  string       $id
     * @param  int          $amount
     * @param  string|null  $actionUser
     * @return object
     */
    public function cancel(string $id, int $amount, $actionUser = null)
    {
        $query = ['Amount' => $amount];
        $actionUser = $actionUser ? ['ActionUser' => $actionUser] : [];

        return $this->client->delete(self::ENDPOINT.$id, [
            \GuzzleHttp\RequestOptions::QUERY => array_merge($query, $actionUser),
        ]);
    }

    /**
     * Get the public key.
     *
     * @return string
     */
    protected function getSystem() : string
    {
        return config('service.hellocash.system');
    }
}
