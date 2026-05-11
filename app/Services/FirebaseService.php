<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging as FirebaseMessaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

/**
 * Wraps the Firebase Admin SDK for server-side operations.
 *
 *  - Firestore: communicates over the REST API (no gRPC required), which makes
 *    it work everywhere PHP runs — including a vanilla Laragon stack on Windows.
 *  - FCM: uses kreait/firebase-php for push messaging.
 *  - Auth:   custom-token minting so the browser can sign in as a Laravel user.
 *
 * All writes are tolerant of a missing service-account file: the method logs a
 * warning and returns silently so the rest of the application keeps working
 * during development.
 */
class FirebaseService
{
    private ?Factory $factory = null;

    private ?FirebaseMessaging $messaging = null;

    private ?FirebaseAuth $auth = null;

    public function isConfigured(): bool
    {
        return (bool) $this->credentialsPath() && (bool) config('firebase.project_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Firestore writes (REST API)
    |--------------------------------------------------------------------------
    */

    public function pushNotification(
        User $user,
        string $type,
        string $title,
        string $body,
        array $data = [],
        ?string $actionUrl = null,
    ): ?string {
        return $this->createDocument(
            "users/{$user->id}/notifications",
            [
                'type'       => $type,
                'title'      => $title,
                'body'       => $body,
                'data'       => $data,
                'action_url' => $actionUrl,
                'is_read'    => false,
                'read_at'    => null,
                'created_at' => now()->toIso8601String(),
            ]
        );
    }

    public function pushMessage(
        User $from,
        User $to,
        string $body,
        ?string $subject = null,
        ?string $parentId = null,
    ): ?string {
        $payload = [
            'from'       => ['id' => (string) $from->id, 'name' => $from->full_name, 'role' => $from->role],
            'to'         => ['id' => (string) $to->id,   'name' => $to->full_name,   'role' => $to->role],
            'subject'    => $subject,
            'body'       => $body,
            'parent_id'  => $parentId,
            'is_read'    => false,
            'read_at'    => null,
            'created_at' => now()->toIso8601String(),
        ];

        // Mirror the message into both inboxes so each side can listen on /users/{me}/messages.
        $primaryId = $this->createDocument("users/{$to->id}/messages",   $payload);
        $this->createDocument("users/{$from->id}/messages", $payload);

        return $primaryId;
    }

    /*
    |--------------------------------------------------------------------------
    | FCM push (via kreait/firebase-php — REST under the hood, no gRPC)
    |--------------------------------------------------------------------------
    */

    public function sendPush(User $user, string $title, string $body, array $data = []): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $tokens = $this->listFcmTokens($user);
        if (empty($tokens)) {
            return;
        }

        $msg = CloudMessage::new()
            ->withNotification(FcmNotification::create($title, $body))
            ->withData(array_map('strval', $data));

        try {
            $this->messaging()->sendMulticast($msg, $tokens);
        } catch (\Throwable $e) {
            logger()->warning('FCM multicast failed', ['user' => $user->id, 'error' => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Custom token — sign the browser in as a Laravel user
    |--------------------------------------------------------------------------
    */

    public function customTokenFor(User $user): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            return $this->auth()
                ->createCustomToken((string) $user->id, ['role' => $user->role])
                ->toString();
        } catch (\Throwable $e) {
            logger()->warning('Custom token creation failed', ['user' => $user->id, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    private function createDocument(string $collectionPath, array $data): ?string
    {
        if (! $this->isConfigured()) {
            logger()->warning('Firestore: skipping write (not configured)', ['path' => $collectionPath]);

            return null;
        }

        $url = sprintf(
            'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents/%s',
            config('firebase.project_id'),
            $collectionPath,
        );

        try {
            $accessToken = $this->fetchAccessToken();

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post($url, ['fields' => $this->encodeFields($data)]);

            if ($response->failed()) {
                logger()->warning('Firestore write failed', [
                    'path'   => $collectionPath,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return null;
            }

            // Document name has the format "projects/.../documents/users/{id}/notifications/{docId}"
            $name = $response->json('name', '');

            return $name ? basename($name) : null;
        } catch (\Throwable $e) {
            logger()->warning('Firestore createDocument exception', [
                'path' => $collectionPath, 'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Read tokens stored under users/{userId}/fcm_tokens.
     */
    private function listFcmTokens(User $user): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $url = sprintf(
            'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents/users/%s/fcm_tokens',
            config('firebase.project_id'),
            $user->id,
        );

        try {
            $accessToken = $this->fetchAccessToken();
            $response = Http::withToken($accessToken)->acceptJson()->get($url);

            if (! $response->ok()) {
                return [];
            }

            $docs   = $response->json('documents', []) ?? [];
            $tokens = [];
            foreach ($docs as $doc) {
                $value = $doc['fields']['token']['stringValue'] ?? null;
                if ($value) {
                    $tokens[] = $value;
                }
            }

            return $tokens;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Encode a PHP array into Firestore's REST API "fields" shape.
     */
    private function encodeFields(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            $out[$key] = $this->encodeValue($value);
        }

        return $out;
    }

    private function encodeValue(mixed $value): array
    {
        if (is_null($value))    return ['nullValue' => null];
        if (is_bool($value))    return ['booleanValue' => $value];
        if (is_int($value))     return ['integerValue' => (string) $value];
        if (is_float($value))   return ['doubleValue' => $value];
        if ($value instanceof \DateTimeInterface) {
            return ['timestampValue' => $value->format(\DateTimeInterface::RFC3339)];
        }

        if (is_array($value)) {
            // Associative → map, sequential → array
            $isAssoc = array_keys($value) !== range(0, count($value) - 1);

            if ($isAssoc) {
                return ['mapValue' => ['fields' => $this->encodeFields($value)]];
            }

            return ['arrayValue' => ['values' => array_map(fn ($v) => $this->encodeValue($v), $value)]];
        }

        return ['stringValue' => (string) $value];
    }

    /**
     * Get a short-lived OAuth access token using the service-account credentials.
     */
    private function fetchAccessToken(): string
    {
        static $cached = null;
        static $expiresAt = 0;

        if ($cached && time() < $expiresAt - 60) {
            return $cached;
        }

        $creds = new \Google\Auth\Credentials\ServiceAccountCredentials(
            scope: ['https://www.googleapis.com/auth/datastore'],
            jsonKey: $this->credentialsPath(),
        );

        $token = $creds->fetchAuthToken();
        $cached    = $token['access_token'];
        $expiresAt = time() + ($token['expires_in'] ?? 3600);

        return $cached;
    }

    private function factory(): Factory
    {
        return $this->factory ??= (new Factory())->withServiceAccount($this->credentialsPath());
    }

    private function messaging(): FirebaseMessaging
    {
        return $this->messaging ??= $this->factory()->createMessaging();
    }

    private function auth(): FirebaseAuth
    {
        return $this->auth ??= $this->factory()->createAuth();
    }

    private function credentialsPath(): ?string
    {
        $relative = config('firebase.credentials');
        if (! $relative) {
            return null;
        }

        $path = base_path($relative);

        return is_file($path) ? $path : null;
    }
}
