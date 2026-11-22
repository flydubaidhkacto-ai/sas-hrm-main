<?php
namespace App\Services;

use Google\Auth\OAuth2;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;  // composer require firebase/php-jwt
use GuzzleHttp\Client;


class FcmService
{
    /**
     * Send a push notification with FCM HTTP v1
     *
     * @param  string $deviceToken  FCM registration token or APN Token from the device
     * @param  string $title        User-visible title
     * @param  string $body         User-visible body
     * @return array                FCM response JSON
     *
     * @throws \Exception
     */
    public static function send($deviceToken, $title, $body, array $extraData = [])
    {
        if(config('app.env') == 'local') return null;
        if(self::looksLikeFcm($deviceToken))
            return self::sendFcm($deviceToken, $title, $body, $extraData);
        return self::sendApns($deviceToken, $title, $body, $extraData);
    }

    static function looksLikeFcm(string $token): bool
    {
        // At least one colon and longer than 100 chars is a quick rule-of-thumb
        return strlen($token) > 100 && strpos($token, ':') !== false;
    }
    public static function sendFcm($deviceToken, $title, $body, array $extraData = [])
    {
        /* ── 1. Service-account credentials ─────────────────────────────── */
        $serviceAccount = storage_path('app/firebase/prohr-app-f09d9b18df42.json');

        if (! is_file($serviceAccount)) {
            throw new \RuntimeException('FCM service-account file missing');
        }

        $creds  = json_decode(file_get_contents($serviceAccount), true);
        $oauth  = new OAuth2([
            'audience'          => 'https://oauth2.googleapis.com/token',
            'issuer'            => $creds['client_email'],
            'signingAlgorithm'  => 'RS256',
            'signingKey'        => $creds['private_key'],
            'tokenCredentialUri'=> 'https://oauth2.googleapis.com/token',
            'scope'             => 'https://www.googleapis.com/auth/firebase.messaging',
        ]);

        $accessToken = $oauth->fetchAuthToken()['access_token'] ?? null;
        if (! $accessToken) {
            throw new \RuntimeException('Could not fetch Firebase access token');
        }

        /* ── 2. Build the cross-platform message ─────────────────────────── */
        $message = [
            'token'        => $deviceToken,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'data' => $extraData,
            //  Android-only tweaks
            'android' => [
                'priority'     => 'high',
                'notification' => [
                    'channel_id' => 'alerts',   // make sure channel exists in the app
                ],
            ],
            //  iOS-only tweaks
            'apns' => [
                'headers' => [
                    // omit apns-topic if you’re happy with Firebase auto-filling it
                    'apns-priority'  => '10',
                    'apns-push-type' => 'alert',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        /* Remove empty sections so FCM ignores them cleanly */
        $message = array_filter($message, static fn ($v) => $v !== [] && $v !== null);

        /* ── 3. POST to FCM ──────────────────────────────────────────────── */
        $url = sprintf(
            'https://fcm.googleapis.com/v1/projects/%s/messages:send',
            $creds['project_id']
        );

        $response = Http::withToken($accessToken)
            ->post($url, ['message' => $message]);

        if ($response->failed()) {
            throw new \Exception(
                'FCM error: '.$response->status().' '.$response->body()
            );
        }

        return $response->json();
    }

    static function sendApns(
        string $apnsToken,          // 64-char hex from iOS device
        string $title,
        string $body,
        array  $extra = []
    ) {
        $p8      = storage_path('app/apns/AuthKey_78N3Z34P26.p8');  // <- your .p8
        $keyId   = '78N3Z34P26';     // 10-char Key ID
        $teamId  = '74VL5MPXX8';     // your Apple Developer Team ID
        $bundle  = 'com.prohrbd.sas';

        /* 1.  Make JWT */
        $jwt = JWT::encode(
            ['iss' => $teamId, 'iat' => time()],
            file_get_contents($p8),
            'ES256',
            $keyId
        );

        /* 2.  Build JSON payload */
        $payload = array_merge([
            'aps' => [
                'alert' => ['title' => $title, 'body' => $body],
                'sound' => 'default'
            ]
        ], $extra);

        /* 3.  POST to APNs */
        $client = new Client([
            'base_uri' => 'https://api.push.apple.com',
            'http_errors' => false,
            'version' => 2               // HTTP/2
        ]);

        $response = $client->post("/3/device/{$apnsToken}", [
            'headers' => [
                'authorization'  => "bearer {$jwt}",
                'apns-topic'     => $bundle,
                'apns-push-type' => 'alert',
                'apns-priority'  => '10',
            ],
            'json' => $payload
        ]);

        return [
            'status' => $response->getStatusCode(),
            'body'   => (string) $response->getBody()
        ];
    }

}
