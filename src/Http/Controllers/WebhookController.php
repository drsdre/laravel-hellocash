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
     * @param Request $request
     *
     * @return Response
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
     * Handle successful calls on the controller.
     *
     * @return Response
     */
    protected function successMethod()
    {
        return new Response('[OK]', 200);
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @return Response
     */
    protected function missingMethod()
    {
        return new Response('[ERROR]', 401);
    }
}
