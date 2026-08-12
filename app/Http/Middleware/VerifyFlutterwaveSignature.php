<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies that an incoming Flutterwave webhook was actually sent by Flutterwave.
 *
 * Flutterwave signs webhooks differently from Paystack. In the Flutterwave
 * dashboard you configure a secret string; on every webhook Flutterwave
 * echoes that string back in the `verif-hash` header. We compare it
 * verbatim to our stored secret.
 *
 * (Flutterwave does NOT HMAC-sign the payload the way Paystack does, so
 * the strongest guarantee here is that the caller knows our shared secret.)
 *
 * The comparison uses `hash_equals` to avoid timing-attack leaks.
 */
class VerifyFlutterwaveSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.flutterwave.webhook_secret');

        // Fail closed: no configured secret == reject everything.
        if (empty($secret)) {
            Log::warning('Flutterwave webhook rejected: FLUTTERWAVE_WEBHOOK_SECRET is not configured.');
            return response()->json(['error' => 'Webhook not configured.'], 503);
        }

        $signature = $request->header('verif-hash');
        if (empty($signature)) {
            Log::warning('Flutterwave webhook rejected: missing verif-hash header.', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Missing signature.'], 401);
        }

        if (!hash_equals($secret, $signature)) {
            Log::warning('Flutterwave webhook rejected: signature mismatch.', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature.'], 401);
        }

        return $next($request);
    }
}
