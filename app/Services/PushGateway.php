<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Notification;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PushGateway
{
    public function send(DeviceToken $device, Notification $notification): void
    {
        if (config('fudo.push_driver') !== 'fcm') {
            throw new \RuntimeException('FCM is not configured.');
        }
        $credentials = config('fudo.firebase_credentials');
        $project = config('fudo.firebase_project_id');
        if (! $credentials || ! $project || ! is_readable($credentials)) {
            throw new \RuntimeException('Firebase credentials or project ID missing.');
        }
        $token = Cache::remember('fcm.access_token.'.hash('sha256', $credentials.$project), 3000, function () use ($credentials) {
            $auth = new ServiceAccountCredentials(['https://www.googleapis.com/auth/firebase.messaging'], $credentials);

            return $auth->fetchAuthToken()['access_token'];
        });
        $response = Http::withToken($token)->timeout(15)->post('https://fcm.googleapis.com/v1/projects/'.rawurlencode($project).'/messages:send', [
            'message' => ['token' => $device->token, 'notification' => ['title' => $notification->title, 'body' => $notification->body],
                'data' => ['notification_id' => (string) $notification->id, 'cart_id' => (string) $notification->cart_id, 'type' => $notification->type]],
        ]);
        if ($response->failed()) {
            $details = $response->json('error.details', []);
            if (collect($details)->contains(fn ($d) => ($d['errorCode'] ?? null) === 'UNREGISTERED')) {
                $device->delete();

                return;
            }
            // Do not persist response bodies: providers may echo device tokens.
            throw new \RuntimeException('FCM delivery failed with HTTP '.$response->status());
        }
    }
}
