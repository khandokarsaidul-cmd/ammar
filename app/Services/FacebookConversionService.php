<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacebookConversionService
{
    private $pixelId;
    private $accessToken;

    public function __construct()
    {
        $this->pixelId = config('facebook.pixel_id');
        $this->accessToken = config('facebook.access_token');
    }

    public function sendEvent($eventName, $userData, $customData)
    {
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

        $response = Http::post($url, $payload);

        return $response->json();
    }
}
