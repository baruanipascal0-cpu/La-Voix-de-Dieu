<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\PushNotification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Throwable;

class FirebasePushService
{
    public function configured(): bool
    {
        return is_string($this->credentialsPath())
            && is_file($this->credentialsPath());
    }

    public function send(PushNotification $notification): void
    {
        if (! $this->configured() || ! $notification->user_id) {
            return;
        }

        $notification->loadMissing('user');

        $tokens = $notification->user?->deviceTokens()
            ->pluck('token')
            ->filter(fn (?string $token): bool => filled($token))
            ->unique()
            ->values()
            ->all() ?? [];

        if ($tokens === []) {
            return;
        }

        try {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($notification->title, $notification->body))
                ->withData($this->messageData($notification));

            $report = app(Messaging::class)->sendMulticast($message, $tokens);
            $successes = $report->successes()->count();
            $failures = $report->failures()->count();

            $this->deleteDeadTokens($notification, [
                ...$report->invalidTokens(),
                ...$report->unknownTokens(),
            ]);

            if ($successes > 0) {
                $notification->forceFill([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'failed_at' => null,
                    'failure_reason' => $failures > 0
                        ? 'FCM partial delivery: '.$failures.' failure(s).'
                        : null,
                ])->save();

                return;
            }

            $notification->forceFill([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => 'FCM delivery failed for every registered device token.',
            ])->save();
        } catch (Throwable $exception) {
            $notification->forceFill([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $this->failureReason($exception),
            ])->save();

            Log::warning('Firebase push notification failed.', [
                'notification_id' => $notification->id,
                'user_id' => $notification->user_id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function messageData(PushNotification $notification): array
    {
        $data = array_merge($notification->data ?? [], [
            'notification_id' => $notification->id,
            'notificationId' => $notification->id,
            'type' => $notification->type,
            'channel' => $notification->channel,
        ]);

        $payload = [];

        foreach ($data as $key => $value) {
            $key = (string) $key;

            if ($key === '') {
                continue;
            }

            $value = $this->stringify($value);

            if ($value !== null) {
                $payload[$key] = $value;
            }
        }

        return $payload;
    }

    private function stringify(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private function deleteDeadTokens(PushNotification $notification, array $tokens): void
    {
        $hashes = collect($tokens)
            ->filter(fn (string $token): bool => filled($token))
            ->unique()
            ->map(fn (string $token): string => hash('sha256', $token))
            ->values();

        if ($hashes->isEmpty()) {
            return;
        }

        DeviceToken::query()
            ->where('user_id', $notification->user_id)
            ->whereIn('token_hash', $hashes)
            ->delete();
    }

    private function credentialsPath(): ?string
    {
        $credentials = config('services.firebase.credentials')
            ?? config('firebase.projects.app.credentials');

        if (! is_string($credentials) || $credentials === '') {
            return null;
        }

        if (str_starts_with($credentials, '/') || preg_match('/^[A-Z]:\\\\/i', $credentials) === 1) {
            return $credentials;
        }

        return base_path($credentials);
    }

    private function failureReason(Throwable $exception): string
    {
        return substr($exception::class.': '.$exception->getMessage(), 0, 1000);
    }
}
