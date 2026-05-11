import './bootstrap';
import Alpine from 'alpinejs';
import {
    initFirebase,
    listenToNotifications,
    listenToMessages,
    markNotificationRead,
    markAllNotificationsRead,
    setupFcm,
} from './firebase';

window.Alpine = Alpine;
Alpine.start();

// Initialise Firebase (best-effort — if credentials are missing or the user is
// not logged in, every call below becomes a no-op).
(async () => {
    // Only attempt sign-in on pages that have a session (skip the login page).
    if (!document.body.dataset.authenticated) return;

    try {
        const ctx = await initFirebase();
        if (!ctx) return;

        // Expose helpers globally so Alpine components can use them inline.
        window.firebaseNotifications = {
            mark:    markNotificationRead,
            markAll: markAllNotificationsRead,
        };

        // Fan notifications out via a custom event — the topbar listens for it.
        listenToNotifications((items, unreadCount) => {
            window.dispatchEvent(new CustomEvent('firebase-notifications', {
                detail: { items, unreadCount },
            }));
        });

        listenToMessages((items) => {
            const unread = items.filter((m) => !m.is_read && m.to?.id === ctx.uid).length;
            window.dispatchEvent(new CustomEvent('firebase-messages', {
                detail: { items, unread },
            }));
        });

        // Ask for push permission only after the user shows intent
        // (we don't want a permission prompt on every page load).
        window.enablePushNotifications = () => setupFcm();
    } catch (e) {
        console.warn('[Firebase] init failed:', e);
    }
})();
