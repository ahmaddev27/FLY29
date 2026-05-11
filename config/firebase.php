<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server-side Firebase Admin SDK
    |--------------------------------------------------------------------------
    | Path to the service-account JSON file (relative to the project root).
    */
    'credentials' => env('FIREBASE_CREDENTIALS', 'storage/firebase/credentials.json'),

    'project_id'  => env('FIREBASE_PROJECT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Web SDK config — exposed to the frontend
    |--------------------------------------------------------------------------
    | These values are public by design (they only identify the project; real
    | security is enforced by Firestore Rules + the user's auth token).
    */
    'web' => [
        'apiKey'            => env('VITE_FIREBASE_API_KEY'),
        'authDomain'        => env('VITE_FIREBASE_AUTH_DOMAIN'),
        'projectId'         => env('VITE_FIREBASE_PROJECT_ID'),
        'storageBucket'     => env('VITE_FIREBASE_STORAGE_BUCKET'),
        'messagingSenderId' => env('VITE_FIREBASE_MESSAGING_SENDER_ID'),
        'appId'             => env('VITE_FIREBASE_APP_ID'),
        'vapidKey'          => env('VITE_FIREBASE_VAPID_KEY'),
    ],

];
