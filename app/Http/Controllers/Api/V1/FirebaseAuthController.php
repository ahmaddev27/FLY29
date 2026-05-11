<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mints a short-lived custom Firebase Auth token for the currently
 * authenticated Laravel user. The browser uses it to sign in to
 * Firebase Auth and gain read access to its own notifications + messages
 * via Firestore security rules.
 */
class FirebaseAuthController extends Controller
{
    public function __construct(private FirebaseService $firebase) {}

    public function token(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $token = $this->firebase->customTokenFor($user);

        if (! $token) {
            return response()->json([
                'error'   => 'firebase_not_configured',
                'message' => 'Firebase Admin SDK credentials are missing on the server.',
            ], 503);
        }

        return response()->json([
            'token'    => $token,
            'uid'      => (string) $user->id,
            'project_id' => config('firebase.project_id'),
        ]);
    }
}
