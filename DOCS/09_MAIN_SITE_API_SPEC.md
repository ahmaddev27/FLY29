# 🔌 مواصفات تكامل API بين الموقع الرئيسي ونظام الولاء
## Main Site ↔ Loyalty System Integration Specification

> **الإصدار:** 1.0
> **التاريخ:** 2026-05-11
> **المُرسِل:** فريق نظام الولاء (Loyalty Team)
> **المُستلِم:** فريق الموقع الرئيسي (Main Site Team)
> **التصنيف:** سري — للاستخدام الداخلي
> **اللغة:** عربي / المصطلحات التقنية بالإنجليزية

---

## 📑 جدول المحتويات

1. [نظرة عامة](#1-نظرة-عامة)
2. [البنية العامة للتكامل](#2-البنية-العامة-للتكامل)
3. [المصادقة والأمان](#3-المصادقة-والأمان)
4. [الـ Endpoints المطلوبة من Main Site](#4-الـ-endpoints-المطلوبة-من-main-site)
5. [الـ Endpoints التي يوفّرها نظام الولاء](#5-الـ-endpoints-التي-يوفّرها-نظام-الولاء)
6. [أمثلة كاملة (PHP)](#6-أمثلة-كاملة-بـ-php)
7. [معالجة الأخطاء وإعادة المحاولة](#7-معالجة-الأخطاء-وإعادة-المحاولة)
8. [المتطلبات الأمنية](#8-المتطلبات-الأمنية)
9. [خطة الاختبار](#9-خطة-الاختبار)
10. [Checklist للتسليم](#10-checklist-للتسليم)
11. [الدعم والتواصل](#11-الدعم-والتواصل)
12. [الملحقات](#12-الملحقات)

---

## 1. نظرة عامة

### 1.1 الهدف من هذه الوثيقة
توضيح كل نقاط التكامل التقنية بين **الموقع الرئيسي fly29.net** و**نظام ولاء وكلاء 29FLY**، لتمكين فريق Main Site من بناء الـ APIs المطلوبة بشكل دقيق وآمن.

### 1.2 نموذج العمل
- 29FLY يبيع باكجات سياحية وخدمات (فندق، مواصلات) من خلال **وكلاء سفر**.
- لكل وكيل **معرف فريد** في Main Site (`agent_id`).
- نظام الولاء **يكافئ الوكلاء بنقاط** على كل عملية بيع.
- النقاط تتراكم في **محفظتين مستقلتين** (نقدية + باكجات مجانية).
- التصنيف الآلي (Bronze/Silver/Gold/Diamond) حسب عدد الباكجات الشهرية.

### 1.3 تدفق العمل (Workflow)
```
1. وكيل يسجّل عملية بيع على fly29.net
2. Main Site يكتشف أن البائع وكيل مسجّل
3. Main Site يرسل Webhook إلى نظام الولاء بتفاصيل المعاملة
4. نظام الولاء:
   - يحسب النقاط حسب تصنيف الوكيل
   - يضيفها لمحفظتيه
   - يفحص الترقية الفورية للتصنيف
   - يرسل إشعار للوكيل
   - يرد على Main Site بـ 200 OK
5. الوكيل يرى نقاطه في لوحة الولاء فوراً
```

### 1.4 المسؤوليات
| الفريق | المسؤولية |
|--------|----------|
| **Main Site** | إرسال webhook عند كل بيع + توفير APIs للمطابقة |
| **Loyalty System** | استقبال webhook + حساب النقاط + إدارة المحفظتين والتصنيفات |

---

## 2. البنية العامة للتكامل

### 2.1 المخطط
```
┌─────────────────┐                          ┌──────────────────┐
│                 │    POST webhook (push)   │                  │
│   Main Site     │ ───────────────────────→ │  Loyalty System  │
│   fly29.net     │                          │   29fly Loyalty  │
│                 │ ←─────────────────────── │                  │
│                 │  GET reconcile (pull)    │                  │
└─────────────────┘                          └──────────────────┘
```

### 2.2 ملخص نقاط التكامل
| # | الاتجاه | الغرض | الأولوية |
|---|---------|------|---------|
| 1 | Main Site → Loyalty | إرسال معاملة جديدة (webhook) | 🔴 P0 |
| 2 | Loyalty → Main Site | استعلام ملخص يومي للمطابقة | 🟡 P1 |
| 3 | Loyalty → Main Site | استعلام قائمة المعاملات لتاريخ معيّن | 🟡 P1 |
| 4 | Loyalty → Main Site | التحقق من وجود وكيل قبل إنشائه | 🟡 P1 |
| 5 | Main Site → Loyalty | إشعار تعليق وكيل | 🟢 P2 |
| 6 | Main Site → Loyalty | إشعار إلغاء/إرجاع معاملة | 🟢 P2 |

### 2.3 البروتوكولات والتقنيات
- **البروتوكول:** HTTPS فقط (TLS 1.3 موصى به).
- **التنسيق:** JSON.
- **الـ Encoding:** UTF-8.
- **التواقيت:** ISO 8601 مع تحديد المنطقة (مثلاً `2026-11-01T10:30:00Z`).
- **التوقيت المعتمد:** Asia/Riyadh (`UTC+3`).
- **العملة:** USD حصرياً.

---

## 3. المصادقة والأمان

### 3.1 طبقتا الأمان
كل طلب يجب أن يجتاز **طبقتين** من التحقق:

1. **API Key** — في header `X-API-Key`.
2. **HMAC Signature** — في header `X-Signature`.

⚠️ **API Key وحدها لا تكفي.** السبب: لو سُرّبت، المهاجم يستطيع إرسال معاملات وهمية. HMAC Signature تضمن أن **محتوى الطلب نفسه** لم يُعدّل وأن المرسل يملك السر المشترك.

---

### 3.2 الـ Credentials (سنوفّرها لكم)

سنرسل لكم عبر **قناة آمنة** (مثلاً 1Password أو ZIP مشفّر):

```
MAIN_SITE_API_KEY=mk_live_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
MAIN_SITE_WEBHOOK_SECRET=ws_secret_X9Y8Z7W6V5U4T3S2R1Q0P9O8N7M6L5K4
```

> **هذه قيم نموذجية فقط — القيم الفعلية ستُرسل في وقت التسليم.**

### 3.3 آلية حساب HMAC Signature

#### الخطوات:
1. خذ **raw request body** (نص JSON قبل أي parsing).
2. احسب `HMAC-SHA256(body, webhook_secret)`.
3. ضع النتيجة (hex string) في header بصيغة: `sha256=<hex>`.

#### مثال (PHP):
```php
$rawBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$signature = 'sha256=' . hash_hmac('sha256', $rawBody, $webhookSecret);
$headers = [
    'Content-Type: application/json',
    'X-API-Key: ' . $apiKey,
    'X-Signature: ' . $signature,
];
```

#### مثال (Node.js):
```javascript
const crypto = require('crypto');
const body = JSON.stringify(payload);
const signature = 'sha256=' + crypto
  .createHmac('sha256', webhookSecret)
  .update(body)
  .digest('hex');
```

⚠️ **مهم جداً:**
- استخدم **نفس** الـ raw body المرسل للحساب — لا تستخدم body بعد parsing.
- لا تترك مسافات إضافية أو تعديل في JSON بعد توقيعه.
- الحساب case-sensitive — استخدم lowercase hex دائماً.

---

### 3.4 ما الذي نتحقق منه من جانبنا؟

```
1. هل header X-API-Key موجود؟ → لا → 401
2. هل API Key يطابق المسجّل؟ → لا → 401 + log كـ suspicious
3. هل header X-Signature موجود؟ → لا → 401
4. احسب HMAC من raw body
5. قارنه مع X-Signature بطريقة constant-time
6. هل متطابقان؟ → لا → 401 + log
7. تمرير الطلب للمعالجة
```

---

## 4. الـ Endpoints المطلوبة من Main Site

> هذه الـ APIs التي يجب أن **يبنيها Main Site** ليستهلكها نظام الولاء.

---

### 🟡 API #1 (P1): استعلام ملخص اليوم
**Daily Summary Endpoint**

#### السبب
نشغّل **Cron يومي 03:00** يقارن إجمالي معاملاتنا مع إجمالي معاملاتكم لاكتشاف أي **فجوات** (لو ضاع webhook لأي سبب). هذا **خط دفاع ثاني** لضمان أن لا وكيل يخسر نقاطه.

#### المواصفات
- **Method:** `GET`
- **URL:** `https://fly29.net/api/v1/loyalty/transactions/summary`
- **Query Parameters:**
  | الاسم | النوع | إجباري | الوصف |
  |------|------|--------|-------|
  | `date` | string (YYYY-MM-DD) | نعم | تاريخ اليوم المراد ملخصه |

- **Headers:**
  ```http
  Authorization: Bearer <token>
  Accept: application/json
  ```

- **Response (200 OK):**
  ```json
  {
    "date": "2026-11-10",
    "count": 1234,
    "total_amount_usd": 456789.50,
    "by_type": {
      "package": 800,
      "service": 434
    }
  }
  ```

- **Response (404 Not Found):**
  ```json
  { "error": "no_data_for_date" }
  ```

#### الشروط
- يحسب فقط المعاملات الخاصة بـ**وكلاء مسجّلين** في نظام الولاء.
- المعاملات الملغاة/المرتجعة **تُستثنى**.
- النتيجة يجب أن تكون **deterministic** (نفس النتيجة دائماً لنفس التاريخ).

#### معدل الاستخدام
- مرة واحدة فقط يومياً (الساعة 03:00).

---

### 🟡 API #2 (P1): قائمة المعاملات لتاريخ
**Daily List Endpoint**

#### السبب
لو وجدنا فجوة من API #1 (مثلاً Main Site يقول "1234 معاملة" ونحن لدينا "1230") — نحتاج القائمة الكاملة للمعاملات لمقارنة الـ `reference_id` ومعرفة المفقود.

#### المواصفات
- **Method:** `GET`
- **URL:** `https://fly29.net/api/v1/loyalty/transactions/list`
- **Query Parameters:**
  | الاسم | النوع | إجباري | الوصف |
  |------|------|--------|-------|
  | `date` | string (YYYY-MM-DD) | نعم | تاريخ اليوم |
  | `page` | integer | لا (default: 1) | رقم الصفحة |
  | `per_page` | integer | لا (default: 100, max: 500) | عدد الصفوف |

- **Headers:**
  ```http
  Authorization: Bearer <token>
  Accept: application/json
  ```

- **Response (200 OK):**
  ```json
  {
    "data": [
      {
        "reference_id": "TXN-MAIN-998877",
        "agent_id": "AGT-1234",
        "transaction_type": "package",
        "amount_usd": 1500.00,
        "destination": "Thailand",
        "transaction_date": "2026-11-10T10:30:00Z"
      },
      {
        "reference_id": "TXN-MAIN-998878",
        "agent_id": "AGT-5678",
        "transaction_type": "service",
        "amount_usd": 250.00,
        "destination": null,
        "transaction_date": "2026-11-10T11:45:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 100,
      "total_pages": 13,
      "total_count": 1234
    }
  }
  ```

#### الشروط
- نفس قواعد API #1 (وكلاء مسجّلين فقط، استثناء الملغى).
- يدعم Pagination.
- النتائج **مرتبة** بـ `transaction_date` تصاعدياً.

#### معدل الاستخدام
- نادر جداً (فقط عند اكتشاف فجوة، ربما 1-2 مرة شهرياً).

---

### 🟡 API #3 (P1): التحقق من الوكيل
**Agent Verification Endpoint**

#### السبب
عندما يضيف الأدمن وكيلاً جديداً في نظام الولاء، نحتاج **التأكد** أن الـ `agent_id` فعلاً موجود في Main Site وأن بياناته متطابقة (لمنع أخطاء الإدخال).

#### المواصفات
- **Method:** `GET`
- **URL:** `https://fly29.net/api/v1/loyalty/agents/{agent_id}`
- **Path Parameter:**
  - `agent_id` — معرف الوكيل في Main Site (string).

- **Headers:**
  ```http
  Authorization: Bearer <token>
  Accept: application/json
  ```

- **Response (200 OK — موجود):**
  ```json
  {
    "agent_id": "AGT-1234",
    "business_name": "Aladin Travel",
    "license_number": "LIC-998877",
    "country": "SA",
    "city": "Riyadh",
    "email": "contact@aladin.travel",
    "phone": "+966501234567",
    "status": "active",
    "registered_at": "2024-03-15T08:00:00Z"
  }
  ```

- **Response (404 Not Found):**
  ```json
  { "error": "agent_not_found" }
  ```

- **Response (200 OK — معلّق):**
  ```json
  {
    "agent_id": "AGT-1234",
    "business_name": "Aladin Travel",
    "status": "suspended",
    ...
  }
  ```

#### الشروط
- البيانات **حية** (real-time) من قاعدة بيانات Main Site.
- `status` قيم محتملة: `active`, `suspended`, `terminated`.
- البريد والهاتف اختياريين (يمكن أن يكونا `null`).

#### معدل الاستخدام
- منخفض (فقط عند إضافة وكيل جديد، ربما 5-10 مرات يومياً).

---

### 🟢 API #4 (P2 — اختياري): توثيق Postman Collection
**Postman Collection**

#### السبب
لتسهيل التطوير والاختبار، نريد منكم تصدير **Postman Collection** يحتوي على الـ 3 endpoints أعلاه مع أمثلة كاملة.

#### الصيغة المتوقعة
- ملف `.json` بصيغة Postman Collection v2.1.
- متغيرات بيئة (Environment Variables):
  - `{{base_url}}` = `https://fly29.net`
  - `{{token}}` = JWT/Bearer token

---

## 5. الـ Endpoints التي يوفّرها نظام الولاء

> هذه الـ APIs التي **نوفّرها نحن** ليستهلكها Main Site.

---

### 🔴 Endpoint #1 (P0): استقبال المعاملة
**Transaction Ingestion Webhook**

#### السبب
**أهم نقطة تكامل في النظام بأكمله.** كل عملية بيع على Main Site يجب أن تصلنا فوراً عبر هذا الـ Webhook حتى:
- نحسب نقاط الوكيل.
- نحدّث محفظتيه.
- نفحص ترقية تصنيفه.
- نُعلِمه بالنقاط الجديدة.

بدون هذا الـ Endpoint — **النظام بأكمله لا يعمل**.

#### المواصفات
- **Method:** `POST`
- **URL:** `https://loyalty.29fly.com/api/v1/transactions/ingest`
- **Content-Type:** `application/json`

#### Headers المطلوبة
```http
Content-Type: application/json
X-API-Key: mk_live_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
X-Signature: sha256=7c4a8d09ca3762af61e59520943dc26494f8941b
Accept: application/json
```

#### Request Body Schema

```json
{
  "agent_id": "AGT-1234",
  "transaction_type": "package",
  "amount_usd": 1500.00,
  "destination": "Thailand",
  "transaction_date": "2026-11-01T10:30:00Z",
  "reference_id": "TXN-MAIN-998877"
}
```

#### تفصيل الحقول

| الحقل | النوع | إجباري | القيود | الوصف |
|------|------|--------|--------|------|
| `agent_id` | string | ✅ | max 50 | معرف الوكيل في Main Site |
| `transaction_type` | enum | ✅ | `package` \| `service` | نوع المعاملة |
| `amount_usd` | decimal | ✅ | > 0، دقة عُشريين | المبلغ بالدولار |
| `destination` | string\|null | لا | max 100 | الوجهة (للباكجات فقط) |
| `transaction_date` | datetime (ISO 8601) | ✅ | UTC أو مع timezone | وقت المعاملة الفعلي |
| `reference_id` | string | ✅ | max 100، **فريد** | معرف المعاملة في Main Site |

⚠️ **`reference_id` ضروري وفريد:**
- لكل معاملة لها `reference_id` فريد على Main Site.
- نستخدمه كـ **Idempotency Key** لمنع احتساب نفس المعاملة مرتين.
- إذا أرسلتم نفس `reference_id` مرتين — سنتجاهل الثانية بأمان (لن نعطي نقاطاً مضاعفة).
- مثال جيد: `TXN-MAIN-{auto_increment}` أو `INV-{order_number}`.

#### Response: نجاح (200 OK)
```json
{
  "status": "accepted",
  "transaction_id": "TXN-LOY-456789",
  "points_awarded": 4,
  "new_balance": {
    "cash": 1204,
    "package": 1204
  }
}
```

#### Response: تكرار مقصود (200 OK)
```json
{
  "status": "duplicate_ignored",
  "reference_id": "TXN-MAIN-998877",
  "message": "Transaction already processed"
}
```

> **لاحظ:** نعيد 200 وليس 4xx للتكرار، لتجنب أن يحاول Main Site إعادة الإرسال.

#### Response: API Key أو Signature خاطئ (401 Unauthorized)
```json
{
  "status": "unauthorized",
  "error": "invalid_signature"
}
```
**إجراء Main Site:** افحص الإعدادات (API Key، Webhook Secret، آلية الحساب). لا تُعد المحاولة.

#### Response: بيانات غير صالحة (422 Unprocessable Entity)
```json
{
  "status": "validation_failed",
  "errors": {
    "amount_usd": ["The amount_usd must be greater than 0."],
    "transaction_type": ["The transaction type must be 'package' or 'service'."]
  }
}
```
**إجراء Main Site:** أصلح البيانات. لا تُعد المحاولة.

#### Response: وكيل غير موجود (404 Not Found)
```json
{
  "status": "agent_not_found",
  "agent_id": "AGT-9999"
}
```
**إجراء Main Site:** سجّل الخطأ. ربما الوكيل لم يُسجَّل في نظام الولاء بعد.

#### Response: وكيل معلّق (422)
```json
{
  "status": "agent_suspended",
  "transaction_held": true,
  "message": "Transaction saved for later processing when agent is reactivated."
}
```
**إجراء Main Site:** لا شيء — نحن سنعالجها عند تفعيل الوكيل.

#### Response: تجاوز Rate Limit (429 Too Many Requests)
```json
{
  "status": "rate_limit_exceeded",
  "retry_after": 60
}
```
**إجراء Main Site:** انتظر `retry_after` ثانية ثم أعد المحاولة.

#### Response: خطأ في النظام (500 Internal Server Error)
```json
{
  "status": "server_error",
  "message": "Please retry later",
  "retry_after": 60
}
```
**إجراء Main Site:** **يجب** إعادة المحاولة وفق سياسة Exponential Backoff (راجع §7).

---

### 🔴 Endpoint #2 (P0): Health Check
**Health Check Endpoint**

#### السبب
ليتمكن Main Site من التحقق دورياً من أن نظام الولاء يعمل (Monitoring).

#### المواصفات
- **Method:** `GET`
- **URL:** `https://loyalty.29fly.com/api/v1/health`
- **بدون مصادقة.**

#### Response (200 OK)
```json
{
  "status": "ok",
  "version": "1.0.0",
  "timestamp": "2026-11-10T15:30:00Z",
  "checks": {
    "database": "ok",
    "scheduler": "ok"
  }
}
```

#### Response (503 Service Unavailable)
```json
{
  "status": "degraded",
  "checks": {
    "database": "fail"
  }
}
```

---

### 🟢 Endpoint #3 (P2): تعليق وكيل
**Agent Status Sync**

#### السبب
عندما يعلّق Main Site وكيلاً (لمخالفة، انتهاء عقد، إلخ)، يجب أن **نعلّقه أيضاً** في نظام الولاء فوراً لمنعه من:
- تسجيل الدخول.
- استلام نقاط جديدة.
- معالجة طلبات تحويل.

#### المواصفات
- **Method:** `POST`
- **URL:** `https://loyalty.29fly.com/api/v1/agents/sync-status`
- نفس آلية المصادقة (API Key + HMAC).

#### Request Body
```json
{
  "agent_id": "AGT-1234",
  "status": "suspended",
  "reason": "Contract violation - November 2026",
  "effective_at": "2026-11-10T15:00:00Z"
}
```

#### Response (200 OK)
```json
{
  "status": "synced",
  "agent_id": "AGT-1234",
  "new_status": "suspended"
}
```

---

### 🟢 Endpoint #4 (P2): إلغاء/إرجاع معاملة
**Transaction Reversal**

#### السبب
لو ألغى زبون باكجاً بعد البيع (refund)، يجب أن **نسحب** النقاط التي منحناها للوكيل لمنع التلاعب.

#### المواصفات
- **Method:** `POST`
- **URL:** `https://loyalty.29fly.com/api/v1/transactions/reverse`
- نفس آلية المصادقة.

#### Request Body
```json
{
  "reference_id": "TXN-MAIN-998877",
  "reason": "Customer refund",
  "reversed_at": "2026-11-12T10:00:00Z"
}
```

#### Response (200 OK)
```json
{
  "status": "reversed",
  "points_deducted": 4,
  "new_balance": {
    "cash": 1200,
    "package": 1200
  }
}
```

#### Response (404 Not Found)
```json
{
  "status": "transaction_not_found",
  "reference_id": "TXN-MAIN-998877"
}
```

---

## 6. أمثلة كاملة بـ PHP

### 6.1 إرسال webhook (Main Site → Loyalty)

```php
<?php
// config (يجب أن تأتي من .env آمناً، لا تكتبها في الكود)
$apiKey = getenv('LOYALTY_API_KEY');
$webhookSecret = getenv('LOYALTY_WEBHOOK_SECRET');
$endpoint = 'https://loyalty.29fly.com/api/v1/transactions/ingest';

// بيانات المعاملة
$payload = [
    'agent_id'         => 'AGT-1234',
    'transaction_type' => 'package',
    'amount_usd'       => 1500.00,
    'destination'      => 'Thailand',
    'transaction_date' => date('c'), // ISO 8601
    'reference_id'     => 'TXN-MAIN-' . $orderId, // فريد لكل معاملة
];

// تحويل لـ JSON (raw body)
$rawBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// حساب التوقيع
$signature = 'sha256=' . hash_hmac('sha256', $rawBody, $webhookSecret);

// إرسال الطلب
$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $rawBody,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-API-Key: ' . $apiKey,
        'X-Signature: ' . $signature,
    ],
    CURLOPT_TIMEOUT        => 10, // 10 ثوان كحد أقصى
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

// معالجة الاستجابة
if ($error) {
    // خطأ شبكي — جدوله للإعادة
    enqueueForRetry($payload, $attemptNumber);
    logError('Webhook network error', $error);
    return;
}

$data = json_decode($response, true);

switch ($httpCode) {
    case 200:
        // نجاح أو duplicate_ignored — تسجيل النجاح
        logSuccess($payload['reference_id'], $data);
        break;

    case 401:
    case 422:
    case 404:
        // خطأ منطقي — لا تُعد المحاولة، أبلِغ الأدمن
        logError('Webhook permanent failure', $data);
        notifyAdmin($payload, $data);
        break;

    case 429:
        // Rate limit — انتظر ثم أعد
        $retryAfter = $data['retry_after'] ?? 60;
        scheduleRetry($payload, $retryAfter);
        break;

    case 500:
    case 502:
    case 503:
        // خطأ مؤقت — أعد المحاولة بـ Backoff
        enqueueForRetry($payload, $attemptNumber);
        break;

    default:
        logError('Unexpected HTTP code', ['code' => $httpCode, 'response' => $response]);
}
```

### 6.2 آلية إعادة المحاولة (Exponential Backoff)

```php
<?php
// جدول المحاولات
$backoffSchedule = [
    1 => 60,        // المحاولة 1: انتظر 1 دقيقة
    2 => 300,       // المحاولة 2: انتظر 5 دقائق
    3 => 1800,      // المحاولة 3: انتظر 30 دقيقة
    4 => 7200,      // المحاولة 4: انتظر 2 ساعة
    5 => 21600,     // المحاولة 5: انتظر 6 ساعات
    6 => 86400,     // المحاولة 6: انتظر 24 ساعة (الأخيرة)
];

function enqueueForRetry(array $payload, int $attemptNumber): void {
    global $backoffSchedule;

    if ($attemptNumber > 6) {
        // فشل نهائي — أبلِغ الأدمن وأرسل لـ Dead Letter
        moveToDeadLetterQueue($payload);
        notifyAdmin('Webhook failed after 6 attempts', $payload);
        return;
    }

    $delay = $backoffSchedule[$attemptNumber];
    scheduleJob('send_webhook', $payload, $delay, $attemptNumber + 1);
}
```

---

## 7. معالجة الأخطاء وإعادة المحاولة

### 7.1 جدول قرارات إعادة المحاولة

| HTTP Code | الحالة | إعادة المحاولة؟ | السبب |
|-----------|-------|----------------|------|
| 200 | OK / Duplicate | ❌ لا | نُجح (أو متجاهل بأمان) |
| 401 | Unauthorized | ❌ لا | خطأ في الـ Credentials — إصلاح ثم إعادة |
| 404 | Agent Not Found | ❌ لا | منطقي — بلّغ الأدمن |
| 422 | Validation | ❌ لا | البيانات نفسها خاطئة |
| 429 | Rate Limit | ✅ نعم | بعد `retry_after` ثانية |
| 500-503 | Server Error | ✅ نعم | Exponential Backoff |
| Timeout | Network | ✅ نعم | Exponential Backoff |
| Connection Error | Network | ✅ نعم | Exponential Backoff |

### 7.2 سياسة إعادة المحاولة الموصى بها
- **عدد المحاولات:** 6 محاولات (محاولة أولى + 5 إعادات).
- **الفواصل:** 1m → 5m → 30m → 2h → 6h → 24h.
- **بعد 6 إخفاقات:** نقل المعاملة إلى **Dead Letter Queue** + إشعار للأدمن.

### 7.3 Dead Letter Queue
- جدول/Queue منفصل يحتوي على المعاملات التي فشلت 6 مرات.
- يُراجعها فريق Main Site يدوياً.
- لو كانت قابلة لإعادة الإرسال، أعد إرسالها يدوياً عبر واجهة إدارية.

### 7.4 Timeout
- **Connect Timeout:** 5 ثوان.
- **Total Timeout:** 10 ثوان.
- إذا تجاوز — اعتبره فشل واعد المحاولة.

---

## 8. المتطلبات الأمنية

### 8.1 إدارة الـ Credentials
- **لا تكتب** API Key أو Webhook Secret في الكود الـ source.
- استخدم Environment Variables (`.env`).
- استخدم Secret Manager (AWS Secrets Manager، HashiCorp Vault) في Production.
- **لا تُسجّل** القيم في logs.

### 8.2 HTTPS إجباري
- جميع الطلبات يجب أن تكون عبر HTTPS.
- تحقق من شهادة SSL (`CURLOPT_SSL_VERIFYPEER = true`).
- لا تقبل HTTP plain تحت أي ظرف.

### 8.3 Constant-Time Comparison
عند مقارنة Signatures من جانبكم (إن احتجتم)، استخدم `hash_equals` وليس `==`:
```php
// ❌ خطأ — عرضة لـ Timing Attack
if ($computed === $received) { ... }

// ✅ صحيح
if (hash_equals($computed, $received)) { ... }
```

### 8.4 IP Whitelist (اختياري لكن موصى به)
- زوّدونا بـ **IP Range** لخوادمكم.
- سنُقيد قبول الـ Webhooks من هذه الـ IPs فقط.
- يوفّر طبقة دفاع إضافية حتى لو سُرّبت الـ Secrets.

### 8.5 Audit Logging
كل طلب نستقبله نسجّله في `api_logs`:
- IP، User-Agent، Timestamp.
- Headers، Body، Response Code، Duration.
- يُحتفظ بها 90 يوماً.

### 8.6 Rate Limiting
- **حد:** 100 طلب/دقيقة من نفس API Key.
- **عند التجاوز:** 429 + `retry_after`.
- يمكن رفع الحد إذا أُثبتت الحاجة.

### 8.7 تدوير الـ Secrets (Rotation)
- **يجب** تدوير API Key و Webhook Secret كل **90 يوم** (سياسة موصى بها).
- آلية التدوير:
  1. ننشئ Key جديد ونرسله لكم.
  2. نقبل **الـ Key القديم والجديد معاً لمدة 7 أيام**.
  3. بعدها نُلغي القديم.

---

## 9. خطة الاختبار

### 9.1 بيئة Staging
- سنوفّر لكم بيئة Staging على `https://loyalty-staging.29fly.com`.
- API Key و Webhook Secret منفصلان عن Production.
- البيانات وهمية — حرية كاملة للتجربة.

### 9.2 سيناريوهات الاختبار المطلوبة

| # | السيناريو | المتوقع | إجباري |
|---|---------|---------|--------|
| 1 | إرسال webhook صحيح (package, 1500$) | 200 OK + points_awarded | ✅ |
| 2 | إرسال webhook صحيح (service, 250$) | 200 OK + 1 point | ✅ |
| 3 | إرسال نفس reference_id مرتين | الأولى 200 accepted، الثانية 200 duplicate | ✅ |
| 4 | إرسال signature خاطئ | 401 unauthorized | ✅ |
| 5 | إرسال API Key خاطئ | 401 unauthorized | ✅ |
| 6 | إرسال agent_id غير موجود | 404 agent_not_found | ✅ |
| 7 | إرسال amount_usd سالب | 422 validation_failed | ✅ |
| 8 | إرسال transaction_type خاطئ | 422 validation_failed | ✅ |
| 9 | إرسال 150 طلب في دقيقة | بعض الطلبات 429 | ⚠️ موصى |
| 10 | محاكاة Timeout (سنوقف الخدمة 10 ثوان) | retry بعد 1m ينجح | ✅ |
| 11 | محاكاة 500 (مؤقت) | retry ينجح في المحاولة 2 | ✅ |
| 12 | محاكاة 500 لـ 6 محاولات | تُنقل لـ Dead Letter | ⚠️ موصى |

### 9.3 معايير القبول
- ✅ كل السيناريوهات الإجبارية تمر.
- ✅ Webhook latency < 500ms (p95).
- ✅ Success rate ≥ 99.9% خلال أسبوع اختبار.
- ✅ Retry mechanism مُختبر فعلياً.
- ✅ HMAC verification يعمل في الاتجاهين.

### 9.4 جلسة اختبار مشترك
نقترح **جلسة اختبار مشتركة (1-2 ساعة)** قبل الإطلاق نُجري فيها:
1. سيناريوهات Happy Path.
2. سيناريوهات الفشل والـ Retry.
3. أسئلة وأجوبة.

---

## 10. Checklist للتسليم

### 10.1 من جانبنا (نظام الولاء)
- [ ] إرسال `MAIN_SITE_API_KEY` عبر قناة آمنة.
- [ ] إرسال `MAIN_SITE_WEBHOOK_SECRET` عبر قناة آمنة.
- [ ] توفير URL بيئة Staging.
- [ ] توفير URL بيئة Production.
- [ ] جلسة شرح تقني (1 ساعة).
- [ ] دعم خلال فترة الاختبار.

### 10.2 من جانبكم (Main Site)
- [ ] استلام الـ Credentials.
- [ ] توفير IP Range خوادمكم (اختياري لكن موصى).
- [ ] **بناء آلية Webhook** (Endpoint #1) — إرسال على كل بيع.
- [ ] **بناء HMAC Signing** (آلية حساب صحيحة).
- [ ] **بناء Retry Mechanism** (Exponential Backoff).
- [ ] **بناء Dead Letter Queue** للفاشلة بعد 6 محاولات.
- [ ] [P1] **بناء API #1** (Daily Summary).
- [ ] [P1] **بناء API #2** (Daily List).
- [ ] [P1] **بناء API #3** (Agent Verification).
- [ ] [P2] **بناء API #4** (Postman Collection).
- [ ] [P2] **استدعاء Endpoint #3** (Agent Status Sync) عند تعليق وكيل.
- [ ] [P2] **استدعاء Endpoint #4** (Transaction Reversal) عند Refund.
- [ ] **اختبار** كل السيناريوهات الإجبارية على Staging.
- [ ] **تأكيد** أن الـ logs لا تكشف Secrets.
- [ ] **توثيق** آلية التكامل في وثائقكم الداخلية.

### 10.3 قبل الإنتقال لـ Production
- [ ] كل السيناريوهات الإجبارية ✅ على Staging.
- [ ] جلسة اختبار مشترك مكتملة.
- [ ] Credentials Production مُسلَّمة.
- [ ] خطة Rollback متفق عليها.
- [ ] قنوات تواصل (Slack/WhatsApp) جاهزة.

---

## 11. الدعم والتواصل

### 11.1 القناة الرئيسية
- **Slack:** `#loyalty-mainsite-integration` (سنُنشئها)
- **Email:** `integration@29fly-loyalty.com`
- **WhatsApp Group:** للحالات العاجلة فقط

### 11.2 SLA الاستجابة
| النوع | الاستجابة |
|------|-----------|
| **Critical** (Production down) | < 30 دقيقة |
| **High** (خلل في التكامل) | < 4 ساعات |
| **Medium** (استفسار تقني) | < 24 ساعة |
| **Low** (تحسينات) | < 5 أيام عمل |

### 11.3 الاجتماعات المقترحة
- **Kickoff Meeting:** قبل بدء التطوير (1 ساعة).
- **Weekly Sync:** كل أسبوع خلال فترة التطوير (30 دقيقة).
- **Final Review:** قبل الإطلاق (1-2 ساعة).

### 11.4 جهات الاتصال

| الدور | الاسم | البريد | الواتساب |
|------|------|--------|---------|
| Project Owner (29FLY) | _______ | _______ | _______ |
| Loyalty Tech Lead | Ahmad Qaddora | aqaddora96@gmail.com | _______ |
| Main Site Tech Lead | _______ | _______ | _______ |
| QA Engineer | _______ | _______ | _______ |

---

## 12. الملحقات

### 12.1 معجم المصطلحات

| المصطلح | التعريف |
|---------|---------|
| **Webhook** | طلب HTTP يُرسل تلقائياً عند حدث (هنا: عملية بيع) |
| **HMAC** | Hash-based Message Authentication Code — توقيع رقمي |
| **Idempotency** | قابلية تكرار العملية بأمان دون أثر مضاعف |
| **Reference ID** | معرف فريد للمعاملة على Main Site |
| **Dead Letter Queue** | قائمة الطلبات الفاشلة نهائياً للمراجعة اليدوية |
| **Exponential Backoff** | استراتيجية انتظار متزايد بين المحاولات |
| **Rate Limit** | حد أقصى للطلبات في فترة زمنية |
| **Constant-Time Comparison** | مقارنة بدون تسريب معلومات زمنياً |

---

### 12.2 جدول رموز HTTP المستخدمة

| Code | الاسم | المعنى عندنا |
|------|-------|--------------|
| 200 | OK | نجح / مكرر متجاهل |
| 401 | Unauthorized | API Key أو Signature خاطئ |
| 404 | Not Found | الوكيل/المعاملة غير موجودة |
| 422 | Unprocessable Entity | بيانات غير صالحة / وكيل معلّق |
| 429 | Too Many Requests | تجاوز Rate Limit |
| 500 | Internal Server Error | خطأ مؤقت عندنا |
| 502/503 | Bad Gateway / Service Unavailable | خدمة معطّلة مؤقتاً |

---

### 12.3 صيغ التاريخ والوقت

```
ISO 8601 with UTC:        2026-11-01T10:30:00Z
ISO 8601 with timezone:   2026-11-01T13:30:00+03:00 (Asia/Riyadh)
Date only:                2026-11-01
```

**الأفضلية:** استخدم UTC (`Z`) دائماً لتجنب التباس المناطق الزمنية.

---

### 12.4 أمثلة على قيم `reference_id` جيدة

✅ **جيدة:**
- `TXN-MAIN-998877`
- `ORDER-2026-11-0123456`
- `INV-29FLY-XYZ123`
- `BOOKING-{uuid_v4}`

❌ **سيئة:**
- `1` (قصير جداً، غير وصفي)
- `abc` (غير منتظم)
- `TXN-998877` (قد يتعارض مع نظام آخر)

---

### 12.5 مثال HMAC حساب يدوي

**Input:**
- Body: `{"agent_id":"AGT-1234","amount_usd":100}`
- Secret: `my_secret_123`

**Calculation:**
```
HMAC-SHA256('{"agent_id":"AGT-1234","amount_usd":100}', 'my_secret_123')
= 8a7e9b3c2d1f4e5a6b7c8d9e0f1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a
```

**Header:**
```http
X-Signature: sha256=8a7e9b3c2d1f4e5a6b7c8d9e0f1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a
```

---

### 12.6 خريطة المتطلبات حسب الأولوية

```
🔴 P0 - حرج (لا يعمل النظام بدونها):
├── Endpoint #1: Transaction Ingestion (Main Site يرسل)
└── إدارة Credentials + HMAC Signing

🟡 P1 - مهم (لأمان وموثوقية كاملة):
├── API #1: Daily Summary (Main Site يوفّر)
├── API #2: Daily List (Main Site يوفّر)
└── API #3: Agent Verification (Main Site يوفّر)

🟢 P2 - تحسينات (مفيدة لكن قابلة للتأجيل):
├── API #4: Postman Collection
├── Endpoint #3: Agent Status Sync (Main Site يستدعي)
├── Endpoint #4: Transaction Reversal (Main Site يستدعي)
└── IP Whitelist
```

---

### 12.7 الإطار الزمني المقترح

| الأسبوع | المرحلة |
|---------|---------|
| 1 | Kickoff + Setup + استلام Credentials |
| 2 | بناء Endpoint #1 (Webhook) + HMAC |
| 3 | بناء Retry + Dead Letter Queue |
| 4 | اختبار على Staging |
| 5 | بناء APIs #1, #2, #3 |
| 6 | اختبار شامل + Final Review |
| 7 | Soft Launch (10 وكلاء) |
| 8 | Full Launch |

---

## 13. الموافقات

| الدور | الاسم | التوقيع | التاريخ |
|------|------|---------|---------|
| Loyalty Project Owner | _______ | _______ | _______ |
| Main Site Project Owner | _______ | _______ | _______ |
| Loyalty Tech Lead | Ahmad Qaddora | _______ | _______ |
| Main Site Tech Lead | _______ | _______ | _______ |
| Security Officer | _______ | _______ | _______ |

---

**نهاية وثيقة مواصفات التكامل**

---

> **ملاحظة:** هذه الوثيقة حية — أي تعديل يجب توثيقه في قسم Change Log أدناه.

## Change Log

| الإصدار | التاريخ | التغييرات | المؤلف |
|---------|--------|----------|--------|
| 1.0 | 2026-05-11 | الإصدار الأول | Ahmad Qaddora |
