<?php

namespace drsdre\HelloCash\Test\Unit;

use drsdre\HelloCash\Connection;
use drsdre\HelloCash\Test\TestCase;
use drsdre\HelloCash\WebhookController;
use Illuminate\Http\Request;

class WebhookControllerTest extends TestCase
{
    /**
     * @test
     * @group unit
     */
    public function it_verifies_a_webhook()
    {
        $verification = ['foo' => 'bar'];

        $this->mockJsonResponses([$verification]);

        $webhook = new Connection($this->client);

        $controller = new WebhookTestController($webhook);

        $request = Request::create('/', 'GET');

        $response = $controller->handle($request);

        $this->assertEquals($verification, $response);
    }

    /**
     * @test
     * @group unit
     */
    public function it_handles_a_notification_event()
    {
        $webhook = app(Connection::class);
        $controller = new WebhookTestController($webhook);

        $event = [
            'EventTypeId' => 1795,
            'foo' => 'bar',
        ];

        $request = Request::create('/', 'POST', $event);

        $response = $controller->handle($request);

        $this->assertStringEndsWith('handleEventNotification', $response['handler']);
        $this->assertArraySubset($event, $response);
    }

    /**
     * @test
     * @group unit
     */
    public function it_handles_a_create_transfer_notification_event()
    {
        $webhook = app(Connection::class);
        $controller = new WebhookTestController($webhook);

        $event = [
            'EventTypeId' => Connection::CREATE_TRANSFER,
            'foo' => 'bar',
        ];

        $request = Request::create('/', 'POST', $event);

        $response = $controller->handle($request);

        $this->assertStringEndsWith('handleCreateTransfer', $response['handler']);
        $this->assertArraySubset($event, $response);
    }

    /**
     * @test
     * @group unit
     */
    public function it_handles_a_refund_transfer_notification_event()
    {
        $webhook = app(Connection::class);
        $controller = new WebhookTestController($webhook);

        $event = [
            'EventTypeId' => Connection::REFUND_TRANSACTION,
            'foo' => 'bar',
        ];

        $request = Request::create('/', 'POST', $event);

        $response = $controller->handle($request);

        $this->assertStringEndsWith('handleRefundTransaction', $response['handler']);
        $this->assertArraySubset($event, $response);
    }
}

class WebhookTestController extends WebhookController
{
    protected function handleEventNotification(Request $request)
    {
        $request['handler'] = __METHOD__;

        return $request->all();
    }
}
