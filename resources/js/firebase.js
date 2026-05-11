// Firebase Web SDK — real-time notifications + messages + FCM push.
//
// Auth flow:
//   1. POST /firebase/auth-token (session-based)  → custom token + uid
//   2. signInWithCustomToken(token)              → Firebase user object
//   3. onSnapshot(users/{uid}/notifications)     → real-time UI updates
//   4. (optional) request notification permission → getToken() → save to Firestore

import { initializeApp } from 'firebase/app';
import {
    getAuth,
    signInWithCustomToken,
} from 'firebase/auth';
import {
    getFirestore,
    collection,
    doc,
    setDoc,
    updateDoc,
    onSnapshot,
    query,
    orderBy,
    limit,
    serverTimestamp,
} from 'firebase/firestore';
import {
    getMessaging,
    getToken,
    onMessage,
    isSupported as isMessagingSupported,
} from 'firebase/messaging';

const config = {
    apiKey:            import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain:        import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId:         import.meta.env.VITE_FIREBASE_PROJECT_ID,
    storageBucket:     import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
    appId:             import.meta.env.VITE_FIREBASE_APP_ID,
};

const VAPID_KEY = import.meta.env.VITE_FIREBASE_VAPID_KEY;

let app, auth, db, currentUid, messaging;

function isConfigured() {
    return Boolean(config.apiKey && config.projectId);
}

/**
 * Initialise the SDK and sign in as the current Laravel user.
 * Safe to call multiple times — subsequent calls are no-ops.
 */
export async function initFirebase() {
    if (!isConfigured()) {
        console.info('[Firebase] not configured — skipping init.');
        return null;
    }
    if (app) return { app, auth, db, uid: currentUid };

    app  = initializeApp(config);
    auth = getAuth(app);
    db   = getFirestore(app);

    // Fetch a custom token from Laravel and sign in.
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const res = await fetch('/firebase/auth-token', {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        credentials: 'same-origin',
    });

    if (!res.ok) {
        console.warn('[Firebase] auth-token endpoint failed', res.status);
        return null;
    }

    const { token, uid } = await res.json();
    await signInWithCustomToken(auth, token);
    currentUid = uid;

    return { app, auth, db, uid };
}

/**
 * Subscribe to the current user's notifications.
 * @param {(items: Array, unreadCount: number) => void} callback
 */
export function listenToNotifications(callback) {
    if (!db || !currentUid) return () => {};

    const q = query(
        collection(db, 'users', currentUid, 'notifications'),
        orderBy('created_at', 'desc'),
        limit(50),
    );

    return onSnapshot(q, (snap) => {
        const items = snap.docs.map((d) => ({ id: d.id, ...d.data() }));
        const unread = items.filter((n) => !n.is_read).length;
        callback(items, unread);
    });
}

/**
 * Subscribe to the current user's messages inbox.
 */
export function listenToMessages(callback) {
    if (!db || !currentUid) return () => {};

    const q = query(
        collection(db, 'users', currentUid, 'messages'),
        orderBy('created_at', 'desc'),
        limit(50),
    );

    return onSnapshot(q, (snap) => {
        const items = snap.docs.map((d) => ({ id: d.id, ...d.data() }));
        callback(items);
    });
}

export async function markNotificationRead(notificationId) {
    if (!db || !currentUid) return;
    await updateDoc(
        doc(db, 'users', currentUid, 'notifications', notificationId),
        { is_read: true, read_at: serverTimestamp() },
    );
}

export async function markAllNotificationsRead(items) {
    if (!db || !currentUid) return;
    await Promise.all(
        items.filter((i) => !i.is_read).map((i) => markNotificationRead(i.id)),
    );
}

/**
 * Request browser notification permission and register an FCM token for the
 * user's account. No-op if the browser doesn't support FCM.
 */
export async function setupFcm() {
    if (!app || !VAPID_KEY) return null;
    if (!(await isMessagingSupported())) return null;

    messaging = getMessaging(app);

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') return null;

    const sw = await navigator.serviceWorker.register('/firebase-messaging-sw.js');

    const token = await getToken(messaging, {
        vapidKey: VAPID_KEY,
        serviceWorkerRegistration: sw,
    });

    if (token) {
        // Persist this token in Firestore so the server-side FCM sender can find it.
        await setDoc(
            doc(db, 'users', currentUid, 'fcm_tokens', token.slice(0, 40)),
            {
                token,
                device:     'web',
                user_agent: navigator.userAgent,
                created_at: serverTimestamp(),
            },
        );
    }

    // Foreground push handler — Firebase only shows OS notifications for
    // background pushes, so we toast manually when the tab is focused.
    onMessage(messaging, (payload) => {
        window.dispatchEvent(new CustomEvent('fcm-foreground', { detail: payload }));
    });

    return token;
}
