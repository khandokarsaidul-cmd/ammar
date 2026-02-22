<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookConversionService
{
    private $pixelId;
    private $accessToken;

    public function __construct()
    {
        $this->pixelId = config('facebook.pixel_id');
        $this->accessToken = config('facebook.access_token');
    }

    public function sendEvent($eventName, $userData, $customData): array
    {
        if (empty($this->pixelId) || empty($this->accessToken)) {
            return [
                'ok' => false,
                'events_received' => 0,
                'message' => 'Facebook conversion credentials are not configured.',
            ];
        }

        $url = "https://graph.facebook.com/v17.0/{$this->pixelId}/events";

        $payload = [
            'data' => [
                [
                    'event_name' => $eventName,
                    'event_time' => time(),
                    'user_data' => $userData,
                    'custom_data' => $customData,
                    'action_source' => 'website',
                ],
            ],
            'access_token' => $this->accessToken,
        ];

        try {
            $response = Http::timeout(8)->post($url, $payload);
            $responseData = $response->json() ?? [];

            if (!$response->successful()) {
                Log::warning('Facebook Conversion API request failed.', [
                    'event' => $eventName,
                    'status' => $response->status(),
                    'response' => $responseData,
                ]);
            }

            $responseData['ok'] = $response->successful();
            return $responseData;
        } catch (\Throwable $exception) {
            Log::warning('Facebook Conversion API exception.', [
                'event' => $eventName,
                'message' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'events_received' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }
}
