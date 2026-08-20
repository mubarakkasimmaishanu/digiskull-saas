<?php

namespace App\Services;

use App\Helpers\Qs;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send bulk SMS reminders
     *
     * @param array $recipients Array of ['phone' => string, 'name' => string, 'balance' => string/int, ...]
     * @param string $messageTemplate Template text (supports {name}, {balance}, {school})
     * @param string|null $senderId Custom Sender ID
     * @return array
     */
    public function sendBulkReminders(array $recipients, string $messageTemplate, ?string $senderId = null): array
    {
        $schoolName = Qs::getSetting('system_name') ?: 'DigiSkul Academy';
        $sender = $senderId ?: (Qs::getSetting('sms_sender_id') ?: substr($schoolName, 0, 11));
        $provider = Qs::getSetting('sms_provider') ?: config('services.sms.provider', 'log');

        $sentCount = 0;
        $failedCount = 0;
        $logs = [];

        foreach ($recipients as $item) {
            $phone = trim($item['phone'] ?? '');
            if (empty($phone)) {
                $failedCount++;
                continue;
            }

            // Interpolate dynamic variables
            $text = str_replace(
                ['{name}', '{balance}', '{school}', '{student}'],
                [
                    $item['name'] ?? 'Guardian',
                    isset($item['balance']) ? number_format($item['balance']) : '0',
                    $schoolName,
                    $item['student_name'] ?? 'Student'
                ],
                $messageTemplate
            );

            $dispatched = $this->dispatchMessage($phone, $text, $sender, $provider);

            if ($dispatched) {
                $sentCount++;
                $logs[] = [
                    'phone' => $phone,
                    'name' => $item['name'] ?? 'Guardian',
                    'message' => $text,
                    'status' => 'sent',
                    'timestamp' => now()->toIso8601String(),
                ];
            } else {
                $failedCount++;
                $logs[] = [
                    'phone' => $phone,
                    'name' => $item['name'] ?? 'Guardian',
                    'message' => $text,
                    'status' => 'failed',
                    'timestamp' => now()->toIso8601String(),
                ];
            }
        }

        return [
            'success' => $sentCount > 0 || count($recipients) === 0,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'provider' => $provider,
            'sender' => $sender,
            'logs' => $logs,
        ];
    }

    /**
     * Dispatch single SMS message via configured provider
     */
    protected function dispatchMessage(string $phone, string $message, string $sender, string $provider): bool
    {
        try {
            // Default mock / log provider (used in tests & standard local instances)
            if ($provider === 'log' || app()->environment('testing') || empty(Qs::getSetting('sms_api_key'))) {
                Log::info("[BULK SMS] Dispatched to {$phone} (Sender: {$sender}): {$message}");
                return true;
            }

            // Generic HTTP REST Provider Integration (e.g. Termii, Twilio, Vonage, Africa's Talking)
            $apiKey = Qs::getSetting('sms_api_key');
            $apiUrl = Qs::getSetting('sms_api_url') ?: 'https://api.sms-provider.com/v1/sms/send';

            $response = Http::timeout(10)->post($apiUrl, [
                'api_key' => $apiKey,
                'to' => $phone,
                'from' => $sender,
                'sms' => $message,
                'type' => 'plain',
                'channel' => 'generic',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("[BULK SMS ERROR] Failed sending to {$phone}: " . $e->getMessage());
            return false;
        }
    }
}
