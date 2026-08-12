<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies that an incoming Paystack webhook was actually sent by Paystack.
 *
 * Paystack signs webhook payloads with:
 *     hash_hmac('sha512', $rawBody, $secretKey)
 * and puts the result in the `X-Paystack-Signature` header.
 *
 * Without this check, any client on the internet can POST a fake
 * `charge.success` payload and get us to provision a free tenant.
 * With this check, only requests carrying a signature computed with
 * our secret key are honored.
 *
 * The comparison uses `hash_equals` to avoid timing-attack leaks.
 */
class VerifyPaystackSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.paystack.webhook_secret');

        // Fail closed: if the secret isn't configured, refuse to accept anything.
        // This prevents a misconfigured production env from silently accepting
        // unsigned webhooks.
        if (empty($secret)) {
            Log::warning('Paystack webhook rejected: PAYSTACK_WEBHOOK_SECRET is not configured.');
            return response()->json(['error' => 'Webhook not configured.'], 503);
        }

        $signature = $request->header('X-Paystack-Signature');
        if (empty($signature)) {
            Log::warning('Paystack webhook rejected: missing X-Paystack-Signature header.', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Missing signature.'], 401);
        }

        $expected = hash_hmac('sha512', $request->getContent(), $secret);

        if (!hash_equals($expected, $signature)) {
            Log::warning('Paystack webhook rejected: signature mismatch.', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature.'], 401);
        }

        return $next($request);
    }
}
