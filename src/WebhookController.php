<?php

namespace drsdre\HelloCash;

use drsdre\HelloCash\Requests\Connection;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

abstract class WebhookController extends Controller
{
    protected $webhook;

    public function __construct(Connection $webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * Handle an incoming request.
     *
     * Handle a GET verification request or a POST notification.
     *
     * @param  Illuminate\Http\Request $request
     * @return mixed
     */
    public function handle(Request $request)
    {
        if ($request->method() == 'GET') {
            return $this->verify();
        }

        return $this->handleNotification($request);
    }

    /**
     * Handle a POST notification.
     *
     * @param  Illuminate\Http\Request $request
     * @return mixed
     */
    protected function handleNotification(Request $request)
    {
        switch ($request->get('EventTypeId')) {
            case Connection::CREATE_TRANSFER:
                return $this->handleCreateTransaction($request);
            case Connection::REFUND_TRANSACTION:
                return $this->handleRefundTransaction($request);
            default:
                return $this->handleEventNotification($request);
        }
    }

    /**
     * Handle any other type of event notification.
     *
     * @param Request $request
     * @return mixed
     */
    abstract protected function handleEventNotification(Request $request);

    /**
     * Verify a webhook.
     *
     * @return array
     */
    protected function verify() : array
    {
        return (array) $this->webhook->getAuthorizationCode();
    }
}
