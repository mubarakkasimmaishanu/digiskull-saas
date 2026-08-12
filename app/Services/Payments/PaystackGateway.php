<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Server-side verification against Paystack's own API.
 *
 * Even after we validate a webhook signature, we do NOT trust the
 * webhook body's amount / status fields — a compromised secret or
 * a replay could still lie. We call Paystack's canonical verify
 * endpoint with the reference and use ONLY what Paystack returns
 * to decide whether to provision a tenant.
 *
 * Paystack response shape (relevant bits):
 * {
 *   "status": true,
 *   "data": {
 *     "status": "success",      // "success" | "failed" | "abandoned"
 *     "reference": "EDUOS-XYZ",
 *     "amount": 2500000,        // MINOR units (kobo)
 *     "currency": "NGN"
 *   }
 * }
 */
class PaystackGateway
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $verifyUrl,
    ) {}

    public function verify(string $reference): GatewayVerificationResult
    {
        if ($this->secretKey === '') {
            Log::error('Paystack verify skipped: PAYSTACK_SECRET_KEY is empty.');
            return GatewayVerificationResult::failure($reference, 'gateway_not_configured');
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->timeout(15)
                ->get(rtrim($this->verifyUrl, '/') . '/' . urlencode($reference));
        } catch (\Throwable $e) {
            Log::error('Paystack verify HTTP error.', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return GatewayVerificationResult::failure($reference, 'network_error');
        }

        if (!$response->ok()) {
            Log::warning('Paystack verify returned non-2xx.', [
                'reference' => $reference,
                'status' => $response->status(),
            ]);
            return GatewayVerificationResult::failure($reference, 'http_' . $response->status());
        }

        $body = $response->json();
        $data = $body['data'] ?? [];

        if (($body['status'] ?? false) !== true || ($data['status'] ?? null) !== 'success') {
            return GatewayVerificationResult::failure(
                $reference,
                'gateway_status_' . ($data['status'] ?? 'unknown'),
                $body ?? []
            );
        }

        return new GatewayVerificationResult(
            successful: true,
            reference: (string) ($data['reference'] ?? $reference),
            // Paystack amounts are ALREADY in minor units (kobo). Cast to int
            // to be defensive against JSON floats.
            amountMinor: (int) ($data['amount'] ?? 0),
            currency: strtoupper((string) ($data['currency'] ?? 'NGN')),
            raw: $body ?? [],
        );
    }
}
