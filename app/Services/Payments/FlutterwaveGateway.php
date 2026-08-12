<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Server-side verification against Flutterwave's API.
 *
 * Flutterwave returns amounts in MAJOR units (naira, not kobo), so
 * we multiply by 100 here to keep the whole system on a single
 * minor-unit contract (see GatewayVerificationResult).
 *
 * Response shape:
 * {
 *   "status": "success",
 *   "data": {
 *     "status": "successful",      // "successful" | "failed" | "pending"
 *     "tx_ref": "EDUOS-XYZ",
 *     "amount": 25000,             // MAJOR units (naira)
 *     "currency": "NGN"
 *   }
 * }
 */
class FlutterwaveGateway
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $verifyUrl,
    ) {}

    public function verify(string $reference): GatewayVerificationResult
    {
        if ($this->secretKey === '') {
            Log::error('Flutterwave verify skipped: FLUTTERWAVE_SECRET_KEY is empty.');
            return GatewayVerificationResult::failure($reference, 'gateway_not_configured');
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->timeout(15)
                ->get($this->verifyUrl, ['tx_ref' => $reference]);
        } catch (\Throwable $e) {
            Log::error('Flutterwave verify HTTP error.', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            return GatewayVerificationResult::failure($reference, 'network_error');
        }

        if (!$response->ok()) {
            Log::warning('Flutterwave verify returned non-2xx.', [
                'reference' => $reference,
                'status' => $response->status(),
            ]);
            return GatewayVerificationResult::failure($reference, 'http_' . $response->status());
        }

        $body = $response->json();
        $data = $body['data'] ?? [];

        if (($body['status'] ?? null) !== 'success' || ($data['status'] ?? null) !== 'successful') {
            return GatewayVerificationResult::failure(
                $reference,
                'gateway_status_' . ($data['status'] ?? 'unknown'),
                $body ?? []
            );
        }

        // Flutterwave gives us naira; normalize to kobo so callers can compare
        // apples-to-apples against Paystack results.
        $amountMinor = (int) round(((float) ($data['amount'] ?? 0)) * 100);

        return new GatewayVerificationResult(
            successful: true,
            reference: (string) ($data['tx_ref'] ?? $reference),
            amountMinor: $amountMinor,
            currency: strtoupper((string) ($data['currency'] ?? 'NGN')),
            raw: $body ?? [],
        );
    }
}
