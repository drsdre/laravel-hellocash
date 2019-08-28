<?php

namespace drsdre\HelloCash\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Notifications\Notifiable;
use Symfony\Component\HttpFoundation\Response;
use drsdre\HelloCash\Http\Middleware\VerifyWebhookSignature;

class WebhookController extends Controller
{
	/**
	 * Create a new WebhookController instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		if (config('hellocash.webhook_secret')) {
			$this->middleware(VerifyWebhookSignature::class);
		}
	}

	/**
	 * Handle a HelloCash webhook call.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handleWebhook(Request $request)
	{
		$payload = json_decode($request->getContent(), true);
		$method = 'handle'.Str::studly(str_replace('.', '_', $payload['type']));

		if (method_exists($this, $method)) {
			return $this->{$method}($payload);
		}

		return $this->missingMethod();
	}

	/**
	 * Handle customer updated.
	 *
	 * @param  array  $payload
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function handleCustomerUpdated(array $payload)
	{
		if ($user = $this->getUserByStripeId($payload['data']['object']['id'])) {
			$user->updateDefaultPaymentMethodFromStripe();
		}

		return $this->successMethod();
	}

	/**
	 * Handle successful calls on the controller.
	 *
	 * @param  array  $parameters
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function successMethod($parameters = [])
	{
		return new Response('Webhook Handled', 200);
	}

	/**
	 * Handle calls to missing methods on the controller.
	 *
	 * @param  array  $parameters
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function missingMethod($parameters = [])
	{
		return new Response;
	}
}