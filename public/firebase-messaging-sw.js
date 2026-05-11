// Service worker for Firebase Cloud Messaging background notifications.
//
// IMPORTANT: this file must be served from the site root (/firebase-messaging-sw.js).
// We use the firebase-app-compat scripts because service workers cannot use
// ES modules in all browsers yet.

importScripts('https://www.gstatic.com/firebasejs/10.13.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.13.0/firebase-messaging-compat.js');

// The same config that initFirebase() uses on the page. Filled in at deploy time
// by replacing the placeholders below (or just paste your real values once).
firebase.initializeApp({
    apiKey:            'PLACEHOLDER_API_KEY',
    authDomain:        'PLACEHOLDER_AUTH_DOMAIN',
    projectId:         'PLACEHOLDER_PROJECT_ID',
    storageBucket:     'PLACEHOLDER_STORAGE_BUCKET',
    messagingSenderId: 'PLACEHOLDER_MESSAGING_SENDER_ID',
    appId:             'PLACEHOLDER_APP_ID',
});

const messaging = firebase.messaging();

// Background notifications: Firebase auto-shows them, but we customise here.
messaging.onBackgroundMessage((payload) => {
    const { title = 'إشعار جديد', body = '' } = payload.notification ?? {};

    self.registration.showNotification(title, {
        body,
        icon:  '/favicon.png',
        badge: '/favicon.png',
        dir:   'rtl',
        lang:  'ar',
        data:  payload.data ?? {},
    });
});

// Clicking the OS notification opens the action URL if provided.
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.action_url || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((wins) => {
            for (const w of wins) {
                if (w.url.includes(url) && 'focus' in w) return w.focus();
            }
            return clients.openWindow(url);
        }),
    );
});
