<?php

namespace App\Services\Payments;

/**
 * Value object returned by gateway `verify($reference)` calls.
 *
 * Kept intentionally small and immutable so callers cannot mutate it
 * between the verify step and the provisioning step. All money values
 * are in MINOR UNITS (kobo / cents) to avoid float-comparison bugs
 * when we compare against the expected amount.
 */
final class GatewayVerificationResult
{
    public function __construct(
        public readonly bool $successful,
        public readonly string $reference,
        /** Amount actually charged, in minor units (kobo/cents). */
        public readonly int $amountMinor,
        public readonly string $currency,
        /** Original gateway payload for audit/debugging (already redacted upstream). */
        public readonly array $raw,
        /** Non-null on failure; short human-readable reason. */
        public readonly ?string $failureReason = null,
    ) {}

    public static function failure(string $reference, string $reason, array $raw = []): self
    {
        return new self(
            successful: false,
            reference: $reference,
            amountMinor: 0,
            currency: '',
            raw: $raw,
            failureReason: $reason,
        );
    }
}
