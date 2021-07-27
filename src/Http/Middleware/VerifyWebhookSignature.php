<?php

namespace Laravel\Cashier\Http\Middleware;

use Closure;
use drsdre\HelloCash\Exceptions\SignatureException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VerifyWebhookSignature
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * The configuration repository instance.
     *
     * @var Config
     */
    protected $config;

    /**
     * Create a new middleware instance.
     *
     * @param  Application $app
     * @param  Config $config
     * @return void
     */
    public function __construct(Application $app, Config $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * Handle the incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->verifyHeader(
                $request->getContent(),
                $request->header('X-Api-Hmac'),
                $this->config->get('hellocash.webhook_secret')
            );
        } catch (SignatureException $exception) {
            $this->app->abort(403);
        }

        return $next($request);
    }

    /**
     * @param string $payload
     * @param string $header_hmac
     * @param string $connection_secret
     *
     * @throws SignatureException
     */
    private function verifyHeader(string $payload, string $header_hmac, string $connection_secret): void
    {
        if ($header_hmac !== hash_hmac('sha256', $payload, $connection_secret, false)) {
            throw new SignatureException();
        }
    }
}
