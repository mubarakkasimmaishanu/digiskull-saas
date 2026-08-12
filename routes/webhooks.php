<?php

use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payment Gateway Webhooks
|--------------------------------------------------------------------------
|
| Registered under `api/webhooks/*` on every central domain by
| bootstrap/app.php. Deliberately sits outside the `web` group so
| CSRF doesn't reject inbound gateway callbacks, and each endpoint
| is wrapped in its provider-specific signature middleware so no
| unsigned/forged request can reach the controller.
|
| Also throttled so a runaway gateway (or a probing attacker) cannot
| DoS the provisioning pipeline.
|
*/

Route::post('/paystack', [PaymentWebhookController::class, 'paystackWebhook'])
    ->middleware(['paystack.signature', 'throttle:60,1'])
    ->name('webhook.paystack');

Route::post('/flutterwave', [PaymentWebhookController::class, 'flutterwaveWebhook'])
    ->middleware(['flutterwave.signature', 'throttle:60,1'])
    ->name('webhook.flutterwave');
