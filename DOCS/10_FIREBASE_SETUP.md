# 🔥 Firebase Setup Guide
## دليل إنشاء وتهيئة Firebase لنظام الإشعارات والرسائل

> **الهدف:** Firestore للتخزين Real-time + FCM للـ Push Notifications
> **المدة المتوقعة:** 5-10 دقائق
> **النتيجة:** 3 مجموعات مفاتيح (Web config + VAPID key + Service Account JSON)

---

## 1. إنشاء Firebase Project

1. اذهب إلى **[https://console.firebase.google.com](https://console.firebase.google.com)** وسجّل الدخول بحساب Google.
2. اضغط **"Add project"** (أو "Create a project").
3. **اسم المشروع:** `fly29-loyalty` (أو أي اسم).
4. **Google Analytics:** اختر **"Disable"** (مش محتاجها الآن).
5. اضغط **"Create project"** وانتظر دقيقتين.

---

## 2. تسجيل Web App + الحصول على Web Config

1. من Project Overview، اضغط أيقونة **`</>`** (Add web app).
2. **App nickname:** `29FLY Loyalty Web`.
3. **لا تفعّل** Firebase Hosting (نحن على Laravel).
4. اضغط **"Register app"**.
5. **انسخ كامل الـ `firebaseConfig` object** — رح يطلعلك شيء بهذا الشكل:
   ```js
   const firebaseConfig = {
     apiKey: "AIza...",
     authDomain: "fly29-loyalty.firebaseapp.com",
     projectId: "fly29-loyalty",
     storageBucket: "fly29-loyalty.appspot.com",
     messagingSenderId: "1234567890",
     appId: "1:1234567890:web:abc123"
   };
   ```
6. احفظ هذه القيم — رح تحطّها في `.env`.

---

## 3. تفعيل Firestore Database

1. من القائمة الجانبية: **Build → Firestore Database**.
2. اضغط **"Create database"**.
3. **اختر "Production mode"** (مش test mode — أأمن، وسنضبط الـ rules لاحقاً).
4. **Location:** `eur3 (europe-west)` أو `us-central1` — اختر الأقرب لك.
5. اضغط **"Enable"**.

### ضبط Security Rules أولية
بعد إنشاء الـ database، اذهب لـ تبويب **Rules** وألصق:

```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {

    // Each user can read & manage only their own notifications + messages
    match /users/{userId}/notifications/{docId} {
      allow read, update, delete: if request.auth != null && request.auth.uid == userId;
      allow create: if false; // server-side only via Admin SDK
    }

    match /users/{userId}/messages/{docId} {
      allow read, update: if request.auth != null && request.auth.uid == userId;
      allow create: if request.auth != null;
    }

    // FCM tokens per user
    match /users/{userId}/fcm_tokens/{tokenId} {
      allow read, write: if request.auth != null && request.auth.uid == userId;
    }
  }
}
```

اضغط **"Publish"**.

---

## 4. تفعيل Cloud Messaging (FCM) + VAPID Key

1. من القائمة الجانبية: **⚙️ Project Settings → تبويب Cloud Messaging**.
2. انزل لقسم **"Web configuration"** → **"Web Push certificates"**.
3. اضغط **"Generate key pair"**.
4. **انسخ الـ Key** اللي يبتدي بـ `B...` — هذه الـ VAPID key.

---

## 5. إنشاء Service Account للـ Backend (Admin SDK)

1. من نفس صفحة Project Settings → تبويب **"Service accounts"**.
2. تأكد أن "Firebase Admin SDK" مختار.
3. اختر **"PHP"** كلغة (تظهر تعليمات لها).
4. اضغط **"Generate new private key"**.
5. سيتنزّل ملف JSON باسم زي `fly29-loyalty-firebase-adminsdk-xxxxx.json`.
6. **حركة مهمة:** ضع هذا الملف في:
   ```
   storage/firebase/credentials.json
   ```
   هذا الملف **مفقفل** ولا يُرفع لـ Git (سنضيفه لـ `.gitignore`).

---

## 6. ضع القيم في `.env`

افتح `.env` وأضف:

```bash
# Firebase — Web SDK (public values, safe to expose to frontend)
VITE_FIREBASE_API_KEY=AIza...
VITE_FIREBASE_AUTH_DOMAIN=fly29-loyalty.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=fly29-loyalty
VITE_FIREBASE_STORAGE_BUCKET=fly29-loyalty.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=1234567890
VITE_FIREBASE_APP_ID=1:1234567890:web:abc123

# Firebase — FCM VAPID key for web push (public)
VITE_FIREBASE_VAPID_KEY=B...

# Firebase — Admin SDK (server-side, secret)
FIREBASE_CREDENTIALS=storage/firebase/credentials.json
FIREBASE_PROJECT_ID=fly29-loyalty
```

---

## 7. سلّم القيم لي

لما تخلص الخطوات، أعطيني:

1. ✅ ملف Web config (الـ JS object من خطوة 2)
2. ✅ VAPID key (من خطوة 4)
3. ✅ تأكيد إنك نزّلت ملف service account وحطيته في `storage/firebase/credentials.json`

وأنا بكمّل التنفيذ.

---

## 📁 خريطة الـ Firestore (مرجع تقني)

```
users/{userId}/
├── notifications/{notificationId}
│   ├── type: "tier_upgraded" | "redemption_approved" | ...
│   ├── title: string
│   ├── body: string
│   ├── data: { tier?, redemption_id?, ... }
│   ├── action_url: string
│   ├── is_read: boolean
│   ├── read_at: timestamp | null
│   └── created_at: timestamp
│
├── messages/{messageId}
│   ├── from: { id, name, role }
│   ├── to: { id, name, role }
│   ├── subject: string
│   ├── body: string
│   ├── parent_id: string | null
│   ├── is_read: boolean
│   ├── read_at: timestamp | null
│   └── created_at: timestamp
│
└── fcm_tokens/{tokenId}
    ├── token: string
    ├── device: "web" | "android" | "ios"
    └── created_at: timestamp
```

`userId` في Firestore = نفس `users.id` من MySQL (نقوم بإنشاء custom token من السيرفر).

---

## 🆘 لو في مشكلة

- **"Permission denied"** في console → تأكد إنك صاحب الـ project أو عندك صلاحية Owner/Editor.
- **VAPID key مش ظاهر** → جرّب refresh الصفحة.
- **ملف JSON ضاع** → ولّد واحد جديد، الأول بطّل صلاحياته.
