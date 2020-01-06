# HelloCash for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/drsdre/laravel-hellocash.svg?style=flat-square)](https://packagist.org/packages/drsdre/laravel-hellocash)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://travis-ci.org/drsdre/laravel-hellocash.svg)](https://travis-ci.org/drsdre/laravel-hellocash)
[![Quality Score](https://img.shields.io/scrutinizer/g/drsdre/laravel-hellocash.svg?style=flat-square)](https://scrutinizer-ci.com/g/drsdre/laravel-hellocash)

[![HelloCash logo](http://www.belcash.com/img/0189/221.png "HelloCash logo")](http://www.belcash.com/helloservices)

This package provides an interface for the [HelloCash](http://www.belcash.com/helloservices) API. It handles the **Invoices**, and **Payments**, as well as **Webhooks**.

Check out the documentation for more information: https://api-et.hellocash.net/docs/

**Note:** This project is not an official package, and I'm not affiliated with HelloCash in any way.

## Table of Contents

- [Setup](#setup)
    - [Installation](#installation)
    - [Service Provider](#service-provider)
    - [Configuration](#configuration)
- [Handling Webhooks](#handling-webhooks)
    - [Extend the controller](#extend-the-controller)
    - [Define the route](#define-the-route)
    - [Exclude from CSRF protection](exclude-from-csrf-protection)
- [API Methods](#api-methods)
    - [Invoices](#invoices)
        - [Validate an invoice](#validate-an-invoice)
        - [Create an invoice](#create-an-invoice)
        - [Get the status of an invoice](#get-status-of-an-invoice)
        - [Get a list of invoices](#get-a-list-of-invoices)
        - [Remove an invoice](#remove-an-invoice)
    - [Transfers](#transfers)
        - [Validate a new transfer](#validate-a-new-transfer)
        - [Create a new transfer](#create-a-new-transfer)
        - [Replace a transfer](#replace-a-transfer)
        - [Get a list of transfers](#get-list-of-transfers)
        - [Find a transfer by id](#find-a-transfer)
        - [Cancel a transfer](#cancel-a-transfer)
        - [Authorize transfers](#authorize-transfers)
    - [Webhooks](#webhooks)
        - [Get an authorization code](#get-an-authorization-code)
- [Exceptions](#exceptions)
- [Tests](#tests)

## Setup

#### Installation

Install the package through Composer.

This package requires Laravel 5.0 or higher, and uses Guzzle to make API calls. Use the appropriate version according to your dependencies.

| HelloCash for Laravel   | Guzzle  | Laravel |
|-----------------------------|---------|---------|
| ~1.0                        | ~5.0    | ~5.0    |
| ~2.0                        | ~6.0    | ~5.0    |
| ~3.0                        | ~6.0    | ~5.5    |

```
composer require drsdre/laravel-hellocash
```

#### Service Provider

This package supports auto-discovery for Laravel 5.5.

If you are using an older version, add the following service provider in your `config/app.php`.

```php
'providers' => [
    drsdre\HelloCash\HelloCashServiceProvider::class,
],
```

#### Configuration

Add the following array in your `config/services.php`.

```php
'hellocash' => [
    'principal' => env('HELLOCASH_PRINCIPAL'),
    'credentials' => env('HELLOCASH_CREDENTIALS'),
    'system' => env('HELLOCASH_SYSTEM'),
],
```

The `principal`, `credentials` and `system` data you get from HelloCash.

> Read more about API authentication in the documentation: https://api-et.hellocash.net/docs/#/Authenticate

## Handling Webhooks

HelloCash supports Webhooks, and this package offers a controller which can be extended to handle incoming notification events.

> Read more about the Webhooks on the wiki: https://api-et.hellocash.net/docs/#/Connection

### Extend the controller

You can make one controller to handle all the events, or make a controller for each event. Either way, your controllers must extend the `drsdre\HelloCash\WebhookController`. The webhook verification is handled automatically.

Hellocash send updates on transfers and invoices through the webhook. To handle those events, you controller must extend the `handleEventNotification` method.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use drsdre\HelloCash\WebhookController as BaseController;

class WebhookController extends BaseController
{
    /**
     * Handle payment notifications.
     *
     * @param  Request $request
     */
    protected function handleEventNotification(Request $request)
    {
        $event = $request->EventData;
    }
}
```

### Define the route

In your `routes/web.php` define the following route for the webhook you have in your profile, replacing the URI(s) and your controller(s) accordingly.

```php
Route::match(['post', 'get'], 'hellocash/webhooks', 'WebhookController@handle');
```

### Exclude from CSRF protection

Don't forget to add your webhook URI(s) to the `$except` array on your `VerifyCsrfToken` middleware.

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'hellocash/webhooks',
    ];
}
```

## API Methods

### Postman Collection

A Postman collection is available to test the calls. Make sure to setup an environment with the following keys:

* endpoint: https://api-et.hellocash.net
* principal: _as provided to you_
* credentials: _as provided to you_
* system: _as provided to you_

[![Run in Postman](https://run.pstmn.io/button.svg)](https://app.getpostman.com/run-collection/5226958988d9957155b1)

### Invoices

##### Validate an invoice

> See: https://api-et.hellocash.net/docs/#!/Invoice/invoice_verify

```php
$invoice = app(drsdre\HelloCash\Requests\Invoice::class);

$invoiceCode = $invoice->validate(100, [...]);
```

##### Create an invoice

> See: https://api-et.hellocash.net/docs/#/Invoice

```php
$invoice = app(drsdre\HelloCash\Requests\Invoice::class);

$invoiceCode = $invoice->create(100, [...]);
```

##### Get the status of an invoice

> See: https://api-et.hellocash.net/docs/#!/Invoice/invoice_findByIdWrap

```php
$invoice = app(drsdre\HelloCash\Requests\Invoice::class);

$response = $invoice->get('175936509216');
```


##### Get a list of invoices

> See: https://api-et.hellocash.net/docs/#!/Invoice/invoice_findWrap

```php
$invoice = app(drsdre\HelloCash\Requests\Invoice::class);

$response = $invoice->list('175936509216');
```

##### Remove an invoice

> See: https://api-et.hellocash.net/docs/#!/Invoice/invoice_deleteById

```php
$invoice = app(drsdre\HelloCash\Requests\Invoice::class);

$response = $invoice->remove('175936509216');
```


### Transfers

##### Validate a transfer

> See: https://api-et.hellocash.net/docs/#!/Transfer/transfer_validate

```php
$transfer = app(drsdre\HelloCash\Transfer::class);

$response = $transfer->validate([]);
```

##### Create a new transfer

> See: https://api-et.hellocash.net/docs/#!/Transfer/transfer_create

```php
$transfer = app(drsdre\HelloCash\Transfer::class);

$response = $transfer->create([]);
```

##### Replace a transfer

> See: https://api-et.hellocash.net/docs/#!/Transfer/transfer_replace

```php
$transfer = app(drsdre\HelloCash\Transfer::class);

$response = $transfer->replace([]);
```

##### Get a list of transfers

> See: https://api-et.hellocash.net/docs/#!/Transfer/transfer_find

```php
$transfer = app(drsdre\HelloCash\Transfer::class);

$transfers = $transfer->list();
```

##### Get a transfer

> See: https://api-et.hellocash.net/docs/#!/Transfer/transfer_findByIdWrap

```php
$transfer = app(drsdre\HelloCash\Transfer::class);

// By transfer ID
$transfers = $transfer->get('252b950e-27f2-4300-ada1-4dedd7c17904');
```

##### Cancel a group of transfers

> See: https://api-et.hellocash.net/docs/#!/Transfer/transfer_cancel

```php
$transfer = app(drsdre\HelloCash\Transfer::class);

$response = $transfer->cancel('175936509216');
```

##### Authorize a group of transfers

> See: https://api-et.hellocash.net/docs/#!/Transfer/transfer_authorize

```php
$transfer = app(drsdre\HelloCash\Transfer::class);

$response = $transfer->authorize();
```

### Webhooks

##### Setup an connection to your webhook URL

> See: https://api-et.hellocash.net/docs/#!/Connection/connection_create

```php
$webhook = app(drsdre\HelloCash\Webhook::class);

$key = $webhook->verify();
```

## Exceptions

When the HelloCash API returns an error, a `drsdre\HelloCash\HelloCashException` is thrown.

For any other HTTP error a `GuzzleHttp\Exception\ClientException` is thrown.

## Tests

Unit tests are triggered by running `phpunit --group unit`.

To run functional tests you have to include a `.env` file in the root folder, containing the credentials (`HELLOCASH_PRINCIPAL`, `HELLOCASH_CREDENTIALS`, `HELLOCASH_SYSTEM`), in order to hit the HelloCash staging API. Then run `phpunit --group functional` to trigger the tests.
