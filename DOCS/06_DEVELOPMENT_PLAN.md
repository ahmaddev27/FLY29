# 🚀 خطة تطوير برنامج ولاء وكلاء 29FLY

> **الإصدار:** 1.0
> **التاريخ:** 2026-05-11
> **المدة المتوقعة:** 14 أسبوع
> **المطور:** فرد واحد - دوام جزئي (3-4 ساعات/يوم)
> **المرجع:** الوثائق من 01 إلى 05

---

## 📋 جدول المحتويات

1. [نظرة شاملة](#1-نظرة-شاملة)
2. [الديزاين سيستيم](#2-الديزاين-سيستيم)
3. [الستاك التقني](#3-الستاك-التقني)
4. [المراحل والـ Sprints](#4-المراحل-والـ-sprints)
5. [الـ APIs المطلوبة من Main Site](#5-الـ-apis-المطلوبة-من-main-site)
6. [بنية المشروع](#6-بنية-المشروع)
7. [Cron Jobs المطلوبة](#7-cron-jobs-المطلوبة)
8. [نقاط الخطر والتخفيف](#8-نقاط-الخطر-والتخفيف)
9. [معايير الانتهاء (DoD)](#9-معايير-الانتهاء-dod)
10. [خطوات التحقق](#10-خطوات-التحقق)
11. [الملاحظات الإدارية](#11-الملاحظات-الإدارية)

---

## 1. نظرة شاملة

### الهدف
بناء نظام Loyalty Program متكامل لوكلاء 29FLY، يربط مبيعاتهم تلقائياً من الموقع الرئيسي بنظام تصنيف ونقاط ومحفظتين مستقلتين، مع لوحات تحكم لثلاث أدوار (Agent / Account Manager / Admin).

### الأرقام الرئيسية
- **69** قصة مستخدم (User Stories) موزّعة على **10** ملاحم.
- **15** حالة استخدام (Use Case) رئيسية.
- **41** قصة P0 (ضروري للإطلاق).
- **4** أدوار: Agent، Account Manager، Admin، Super Admin.
- **15+** جدول DB رئيسي.
- **2** محفظتان مستقلتان لكل وكيل.
- **4** تصنيفات: Bronze → Silver → Gold → Diamond.

### نهج التطوير
- **Vertical Slicing:** كل Sprint ينتج Feature قابلة للاختبار End-to-End.
- **Service Layer:** Controllers ← Services ← Models (طبقية صارمة).
- **Migration-First:** كل ميزة تبدأ بـ DB Schema.
- **Test as you go:** Feature Tests لكل Critical Path.
- **بدون Queue:** كل العمليات Synchronous، المهام الدورية عبر Laravel Scheduler.

---

## 2. الديزاين سيستيم

مستوحى من [fly29.net](https://www.fly29.net/) مع إضافات خاصة بنظام التصنيف.

### الألوان (Color Palette)

| الاستخدام | اللون | Hex |
|----------|------|-----|
| Primary (روابط، Navigation) | أزرق | `#0066CC` |
| Accent (Featured) | برتقالي/ذهبي | `#F59E0B` |
| CTA (أزرار رئيسية) | أخضر/تركواز | `#10B981` |
| Text Primary | أسود فاتح | `#222222` |
| Text Secondary | رمادي | `#666666` |
| Background Primary | أبيض | `#FFFFFF` |
| Background Secondary | رمادي فاتح | `#F5F5F5` |
| Border | رمادي ناعم | `#E5E7EB` |
| Success | أخضر | `#10B981` |
| Warning | برتقالي | `#F59E0B` |
| Danger | أحمر | `#EF4444` |
| Info | أزرق فاتح | `#3B82F6` |

### ألوان التصنيفات (Tier Colors)

| التصنيف | اللون | Hex | الأيقونة |
|---------|------|-----|---------|
| 💎 Diamond | أزرق سماوي | `#3B82F6` | diamond |
| 🥇 Gold | ذهبي | `#F59E0B` | trophy |
| 🥈 Silver | فضي | `#94A3B8` | medal |
| 🥉 Bronze | برونزي | `#A16207` | badge |

### الخطوط (Typography)
- **العربية:** [Cairo](https://fonts.google.com/specimen/Cairo) (Google Fonts) — Fallback: Tahoma, Segoe UI.
- **اللاتينية:** Inter — Fallback: Arial, sans-serif.
- **الأحجام:**
  - `h1`: 2rem (32px) - bold
  - `h2`: 1.5rem (24px) - semibold
  - `h3`: 1.25rem (20px) - semibold
  - `body`: 1rem (16px) - regular
  - `small`: 0.875rem (14px) - regular

### المسافات والمكونات
- **Container max-width:** 1200px
- **Border radius:** 4px (inputs) / 8px (cards) / 12px (modals)
- **Shadow:** `0 2px 4px rgba(0,0,0,0.1)` (cards) / `0 10px 25px rgba(0,0,0,0.15)` (modals)
- **Spacing:** Tailwind scale (4, 8, 12, 16, 24, 32, 48...)
- **Direction:** RTL كامل (`<html dir="rtl" lang="ar">`)

### مكونات UI أساسية (يبنى في Phase 0)
- Button (primary, secondary, danger, ghost, sizes: sm/md/lg)
- Card (default, with-header, stat-card)
- Badge (tier, status, count)
- Input (text, email, password, number, with icon)
- Select / Dropdown
- Modal (sm, md, lg, fullscreen)
- Toast / Alert (success, error, warning, info)
- Table (sortable, with-actions, with-filter)
- Pagination
- Sidebar (collapsible)
- Topbar (with notifications dropdown)
- Tabs
- Tooltip
- Progress Bar
- Empty State
- Loading Spinner / Skeleton

---

## 3. الستاك التقني

| الطبقة | التقنية | الإصدار |
|--------|--------|---------|
| Backend Framework | Laravel | 12.x |
| Language | PHP | 8.3+ |
| Database | MySQL | 8.0 |
| Frontend Engine | Blade | (مدمج) |
| JS Framework | Alpine.js | 3.x |
| CSS Framework | Tailwind CSS | 3.4 |
| Charts | Chart.js | 4.x |
| Icons | Heroicons | 2.x |
| **Queue** | **بدون - Synchronous** | - |
| **Cache** | **File (Laravel default)** | - |
| Scheduler | Laravel Schedule | (مدمج) |
| Mail | SMTP (Mailgun/SendGrid مقترح) | - |
| Excel | Maatwebsite/Laravel-Excel | 3.x |
| PDF | barryvdh/laravel-dompdf | latest |
| 2FA | pragmarx/google2fa-laravel | latest |
| reCAPTCHA | josiasmontag/laravel-recaptchav3 | latest |
| RTL Plugin | tailwindcss-rtl | latest |
| Testing | PHPUnit + Laravel Dusk | (مدمج) |

---

## 4. المراحل والـ Sprints

### 🟢 Phase 0: التأسيس (Foundation) — أسبوع 1

| Sprint | الفترة | الهدف |
|--------|--------|-------|
| 0.1 | 3 أيام | إنشاء المشروع، Tailwind، Alpine، RTL، Folder structure |
| 0.2 | 4 أيام | بناء Design System Components كاملة + Landing Demo Page |

**Deliverable:** صفحة عرض لكل المكونات UI مع توثيقها في Storybook بسيط (Markdown).

---

### 🟢 Phase 1: MVP الأساسي — أسابيع 2-4

| Sprint | الفترة | الهدف |
|--------|--------|-------|
| 1.1 | أسبوع | DB Migrations + Models + Auth System + 2FA |
| 1.2 | أسبوع | Webhook Endpoint + Points Engine + Tier Engine |
| 1.3 | أسبوع | Agent Dashboard + Wallets View + Tier Card |

**Deliverable نهاية Phase 1:** الوكيل يسجّل دخوله، يستقبل Webhook من Main Site (أو Mock)، يرى نقاطه وتصنيفه يترقّى تلقائياً.

---

### 🟢 Phase 2: المحفظتان والاستبدال — أسابيع 5-7

| Sprint | الفترة | الهدف |
|--------|--------|-------|
| 2.1 | أسبوع | Cash Redemption (طلب، حجز، موافقة، رفض) |
| 2.2 | نصف أسبوع | Package Redemption + Free Packages CRUD |
| 2.3 | نصف أسبوع | Notifications (7 أنواع، Sync، RTL Templates) |
| 2.4 | أسبوع | Transaction History + Filters + Export |

**Deliverable نهاية Phase 2:** الوكيل يطلب تحويل/استبدال، الأدمن يوافق/يرفض، الإشعارات تُرسل تلقائياً.

---

### 🟢 Phase 3: لوحة الأدمن الكاملة — أسابيع 8-10

| Sprint | الفترة | الهدف |
|--------|--------|-------|
| 3.1 | أسبوع | Admin Dashboard + Agents CRUD + Excel Import |
| 3.2 | أسبوع | Manual Adjustments (+ Dual Approval) + Settings + AM Management |
| 3.3 | أسبوع | Requests Center + Audit Log + API Logs + Announcements |

**Deliverable نهاية Phase 3:** الأدمن قادر على إدارة كل شيء من الواجهة بدون أي تدخل تقني.

---

### 🟢 Phase 4: مدير الحساب والتقارير — أسابيع 11-12

| Sprint | الفترة | الهدف |
|--------|--------|-------|
| 4.1 | أسبوع | Account Manager Panel + Messaging + Suggested Adjustments |
| 4.2 | أسبوع | 5 Reports + Charts + Export + Reconciliation Command |

**Deliverable نهاية Phase 4:** نظام كامل قابل للإطلاق.

---

### 🟡 Phase 5: ما قبل الإطلاق — أسبوع 13
- Load Testing (k6 - 500 وكيل متزامن).
- Security Audit (OWASP ZAP + Manual).
- Performance Optimization (N+1 fixes، indexes، caching).
- Browser Tests (Laravel Dusk للـ Critical Flows).
- وثائق المستخدم النهائية.

---

### 🟢 Phase 6: الإطلاق — أسبوع 14
- Soft Launch لـ 10-20 وكيل (Early Adopters).
- جمع Feedback + Hot-fixes.
- Full Launch + Webinar.
- مراقبة KPIs أول 30 يوم.

---

## 5. الـ APIs المطلوبة من Main Site

> هذه القائمة **يجب** تسليمها لمبرمج fly29.net في **أول لقاء تكاملي**.

### 🔴 P0 — حرج (لا يعمل النظام بدونها)

#### API #1: Webhook عند كل عملية بيع
- **الاتجاه:** Main Site → نظام الولاء (نحن نوفّر الـ endpoint).
- **Endpoint نوفّره نحن:**
  ```
  POST https://loyalty.29fly.com/api/v1/transactions/ingest
  ```
- **Headers من Main Site:**
  - `Content-Type: application/json`
  - `X-API-Key: <key نوفّره>`
  - `X-Signature: sha256=<HMAC-SHA256(body, secret)>`
- **Body:**
  ```json
  {
    "agent_id": "AGT-1234",
    "transaction_type": "package",  // package | service
    "amount_usd": 1500.00,
    "destination": "Thailand",
    "transaction_date": "2026-11-01T10:30:00Z",
    "reference_id": "TXN-MAIN-998877"
  }
  ```
- **استجابات سنرجعها:**
  | HTTP | Status Body | إجراء Main Site |
  |------|-------------|----------------|
  | 200 | `accepted` | لا شيء |
  | 200 | `duplicate_ignored` | لا شيء (مقصود) |
  | 401 | unauthorized | فحص API Key / Signature |
  | 422 | agent_suspended | حُفظت لدينا، ستعالج لاحقاً |
  | 500 | server_error | **يعيد المحاولة** (Backoff: 1m, 5m, 30m, 2h, 6h) |

#### API #2: HMAC Signature
- يستخدم `webhook_secret` (نوفّره عبر قناة آمنة).
- `signature = 'sha256=' . hash_hmac('sha256', $rawBody, $webhookSecret)`
- يُرسل في header `X-Signature`.

#### API #3: Credentials المطلوبة
- `MAIN_SITE_API_KEY` (نوفّره نحن لـ Main Site).
- `MAIN_SITE_WEBHOOK_SECRET` (نوفّره نحن لـ Main Site).
- IP Range لخوادم Main Site (للـ Whitelist - اختياري).
- اعتماد توقيت Asia/Riyadh على `transaction_date`.

---

### 🟡 P1 — مهم (لميزات إضافية)

#### API #4: Daily Summary (للـ Reconciliation)
- **الاتجاه:** نظام الولاء → Main Site (Main Site يوفّره).
```
GET https://fly29.net/api/v1/loyalty/transactions/summary?date=2026-11-10
Authorization: Bearer <token>
```
**Response:**
```json
{
  "date": "2026-11-10",
  "count": 1234,
  "total_amount_usd": 456789.50
}
```

#### API #5: Daily List (عند اكتشاف فجوة)
```
GET https://fly29.net/api/v1/loyalty/transactions/list?date=2026-11-10&page=1
Authorization: Bearer <token>
```
**Response:**
```json
{
  "data": [
    {
      "reference_id": "TXN-MAIN-998877",
      "agent_id": "AGT-1234",
      "transaction_type": "package",
      "amount_usd": 1500.00,
      "transaction_date": "2026-11-10T10:30:00Z"
    }
  ],
  "pagination": { "current_page": 1, "total_pages": 50, "per_page": 100 }
}
```

#### API #6: التحقق من الوكيل
```
GET https://fly29.net/api/v1/loyalty/agents/{agent_id}
Authorization: Bearer <token>
```
**Response (موجود):**
```json
{
  "agent_id": "AGT-1234",
  "business_name": "Aladin Travel",
  "status": "active",
  "country": "SA",
  "city": "Riyadh",
  "license_number": "LIC-998877"
}
```
**Response (غير موجود):** `404 Not Found`.

---

### 🟢 P2 — تحسينات مستقبلية (اختياري)

#### API #7: Sync Status (Main Site يعلّق وكيل)
```
POST https://loyalty.29fly.com/api/v1/agents/sync-status
Body: { "agent_id": "AGT-1234", "status": "suspended", "reason": "..." }
```

#### API #8: Transaction Reverse (إلغاء معاملة)
```
POST https://loyalty.29fly.com/api/v1/transactions/reverse
Body: { "reference_id": "TXN-MAIN-998877", "reason": "refund" }
```

---

### 📋 Checklist لمبرمج Main Site

سيُسلَّم له هذا الـ Checklist في أول اجتماع:

- [ ] **استلام:** `MAIN_SITE_API_KEY` + `MAIN_SITE_WEBHOOK_SECRET`.
- [ ] **توفير:** IP Range لخوادم Main Site (اختياري للـ Whitelist).
- [ ] **بناء:** إرسال Webhook إلى `/api/v1/transactions/ingest` عند كل عملية بيع.
- [ ] **بناء:** حساب HMAC-SHA256 وإرساله في `X-Signature`.
- [ ] **التزام:** Schema الـ Body المحدد.
- [ ] **ضمان:** `reference_id` فريد لكل معاملة على Main Site.
- [ ] **بناء:** آلية Retry عند 500/timeout (Exponential Backoff).
- [ ] [P1] **بناء:** Endpoint `/loyalty/transactions/summary`.
- [ ] [P1] **بناء:** Endpoint `/loyalty/transactions/list`.
- [ ] [P1] **بناء:** Endpoint `/loyalty/agents/{id}`.
- [ ] [P2] **استدعاء:** Webhook لتعليق وكيل.
- [ ] [P2] **استدعاء:** Webhook لعكس معاملة.

### 🤝 طريقة التواصل
- قناة Slack/WhatsApp مشتركة.
- بيئة Staging قبل Production.
- Postman Collection مشترك.
- مراجعة أسبوعية لحالة التكامل.

---

## 6. بنية المشروع

```
c:\laragon\www\Fly\
├── DOCS/                              # التوثيق
│   ├── 01_USER_STORIES.md
│   ├── 02_USE_CASES.md
│   ├── 03_PRD.md
│   ├── 04_SRS.md
│   ├── 05_ARCHITECTURE_DIAGRAM.md
│   ├── 06_DEVELOPMENT_PLAN.md         ← هذا الملف
│   ├── 07_PROGRESS.md                 ← التتبع اليومي
│   └── 08_TASKS_BACKLOG.md            ← المهام التفصيلية
│
├── app/
│   ├── Actions/                       # Single-action classes
│   │   ├── IngestTransactionAction.php
│   │   ├── ApproveRedemptionAction.php
│   │   ├── RejectRedemptionAction.php
│   │   ├── EvaluateTierAction.php
│   │   └── AdjustPointsAction.php
│   │
│   ├── Console/
│   │   ├── Kernel.php                 # Scheduler registration
│   │   └── Commands/
│   │       ├── EvaluateTiersCommand.php
│   │       ├── ReconcileTransactionsCommand.php
│   │       ├── CleanupExpiredTokensCommand.php
│   │       └── ArchiveOldLogsCommand.php
│   │
│   ├── DTOs/
│   │   ├── TransactionDTO.php
│   │   ├── PointsCalculationDTO.php
│   │   └── TierEvaluationDTO.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── PasswordResetController.php
│   │   │   │   └── TwoFactorController.php
│   │   │   ├── Agent/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── WalletController.php
│   │   │   │   ├── RedemptionController.php
│   │   │   │   ├── TransactionController.php
│   │   │   │   ├── NotificationController.php
│   │   │   │   ├── MessageController.php
│   │   │   │   └── ProfileController.php
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── AgentController.php
│   │   │   │   ├── RedemptionController.php
│   │   │   │   ├── PackageController.php
│   │   │   │   ├── SettingsController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── AuditLogController.php
│   │   │   │   ├── AccountManagerController.php
│   │   │   │   ├── AdjustmentController.php
│   │   │   │   └── AnnouncementController.php
│   │   │   ├── AccountManager/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── AgentController.php
│   │   │   │   ├── MessageController.php
│   │   │   │   └── AdjustmentController.php
│   │   │   └── Api/V1/
│   │   │       └── WebhookController.php
│   │   ├── Middleware/
│   │   │   ├── WebhookAuth.php
│   │   │   ├── VerifyHmacSignature.php
│   │   │   ├── EnsureRole.php
│   │   │   ├── AuditLog.php
│   │   │   └── Verify2FA.php
│   │   ├── Requests/
│   │   │   ├── (form requests لكل endpoint)
│   │   └── Resources/
│   │       └── (API resources)
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Agent.php
│   │   ├── AgentLevel.php
│   │   ├── Transaction.php
│   │   ├── CashWalletPoints.php
│   │   ├── PackageWalletPoints.php
│   │   ├── PointsHistory.php
│   │   ├── RedemptionRequest.php
│   │   ├── TierHistory.php
│   │   ├── FreePackage.php
│   │   ├── SystemSetting.php
│   │   ├── AuditLog.php
│   │   ├── ApiLog.php
│   │   ├── Notification.php
│   │   ├── Message.php
│   │   ├── UserNotificationPreference.php
│   │   └── PendingAdjustment.php
│   │
│   ├── Notifications/                 # Sync (لا ShouldQueue)
│   │   ├── TierUpgradedNotification.php
│   │   ├── TierDowngradedNotification.php
│   │   ├── TierWarningNotification.php
│   │   ├── PointsEarnedNotification.php
│   │   ├── RedemptionApprovedNotification.php
│   │   ├── RedemptionRejectedNotification.php
│   │   ├── NewRedemptionRequestNotification.php
│   │   ├── ManualPointsAddedNotification.php
│   │   └── FreePackageThresholdReachedNotification.php
│   │
│   ├── Policies/
│   │   ├── AgentPolicy.php
│   │   ├── RedemptionPolicy.php
│   │   └── SettingPolicy.php
│   │
│   └── Services/
│       ├── AuthService.php
│       ├── PointsService.php
│       ├── WalletService.php
│       ├── TierService.php
│       ├── RedemptionService.php
│       ├── NotificationService.php
│       ├── SettingsService.php
│       ├── AuditService.php
│       ├── ReportService.php
│       ├── ReconciliationService.php
│       └── MainSiteApiService.php
│
├── database/
│   ├── migrations/                    # كل الجداول
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── AgentLevelsSeeder.php
│   │   ├── SystemSettingsSeeder.php
│   │   ├── AdminUserSeeder.php
│   │   └── FreePackagesSeeder.php
│   └── factories/
│
├── resources/
│   ├── views/
│   │   ├── components/
│   │   │   ├── ui/                    # Design System
│   │   │   │   ├── button.blade.php
│   │   │   │   ├── card.blade.php
│   │   │   │   ├── badge.blade.php
│   │   │   │   ├── input.blade.php
│   │   │   │   ├── select.blade.php
│   │   │   │   ├── modal.blade.php
│   │   │   │   ├── alert.blade.php
│   │   │   │   ├── table.blade.php
│   │   │   │   ├── tabs.blade.php
│   │   │   │   ├── progress.blade.php
│   │   │   │   ├── tier-badge.blade.php
│   │   │   │   └── empty-state.blade.php
│   │   │   ├── forms/
│   │   │   │   ├── form-group.blade.php
│   │   │   │   └── error.blade.php
│   │   │   └── layout/
│   │   │       ├── sidebar.blade.php
│   │   │       ├── topbar.blade.php
│   │   │       └── footer.blade.php
│   │   ├── layouts/
│   │   │   ├── app.blade.php
│   │   │   ├── auth.blade.php
│   │   │   ├── agent.blade.php
│   │   │   ├── admin.blade.php
│   │   │   └── manager.blade.php
│   │   ├── auth/
│   │   │   ├── login.blade.php
│   │   │   ├── forgot-password.blade.php
│   │   │   ├── reset-password.blade.php
│   │   │   └── two-factor.blade.php
│   │   ├── agent/
│   │   ├── admin/
│   │   ├── manager/
│   │   └── emails/                    # RTL Templates
│   │
│   ├── css/
│   │   └── app.css                    # Tailwind + Custom
│   └── js/
│       └── app.js                     # Alpine.js
│
├── routes/
│   ├── web.php                        # Public + Auth
│   ├── api.php                        # Webhook API
│   ├── agent.php                      # Agent panel
│   ├── admin.php                      # Admin panel
│   └── manager.php                    # AM panel
│
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   ├── Webhook/
│   │   ├── Wallet/
│   │   ├── Redemption/
│   │   ├── Tier/
│   │   └── Admin/
│   └── Unit/
│       └── Services/
│
├── .env.example
├── README.md
├── composer.json
├── package.json
└── tailwind.config.js
```

---

## 7. Cron Jobs المطلوبة

كل المهام عبر **Laravel Scheduler** (في `app/Console/Kernel.php`).

| Command | Schedule | الوصف |
|---------|---------|--------|
| `tiers:evaluate` | يومي 02:00 (Asia/Riyadh) | تخفيض التصنيف + تحذير قبل 7 أيام |
| `transactions:reconcile` | يومي 03:00 | مطابقة مع Main Site |
| `tokens:cleanup` | يومي 04:00 | حذف tokens منتهية |
| `logs:archive` | أسبوعي الجمعة 05:00 | أرشفة logs > 90 يومًا |

### إعداد الـ Crontab على الخادم:
```bash
* * * * * cd /path/to/Fly && php artisan schedule:run >> /dev/null 2>&1
```

---

## 8. نقاط الخطر والتخفيف

| # | الخطر | الاحتمال | الأثر | التخفيف |
|---|------|---------|------|---------|
| 1 | تأخر APIs من Main Site | عالي | حرج | بناء Mock + Postman Collection + التواصل المبكر |
| 2 | Laravel 12 طازج، أقل وثائق | متوسط | متوسط | Fallback لـ Laravel 11 إذا واجهنا مشاكل |
| 3 | Race Conditions على المحفظتين | متوسط | عالي | DB Locks + Transactions من Day 1 |
| 4 | بدون Queue → بطء في Webhook | متوسط | متوسط | تحسين Code Path + DB Indexes + SMTP سريع |
| 5 | بدون Queue → فشل إيميل يبطّئ المستخدم | متوسط | متوسط | `try/catch` حول `Mail::send` + Logging الفشل |
| 6 | Config Snapshot معقد | منخفض | متوسط | JSON column + اختبار شامل |
| 7 | RTL في Tailwind | منخفض | منخفض | `tailwindcss-rtl` plugin |
| 8 | Dual Approval Flow | متوسط | متوسط | Mock UI + اختبار سيناريوهات |
| 9 | Excel Import يفشل بسبب صف واحد | متوسط | منخفض | DB Transaction + تقرير الأخطاء |
| 10 | تسرب API Key | منخفض | حرج | Rotation policy + Audit log |

---

## 9. معايير الانتهاء (DoD)

كل Task يُعتبر منجزاً فقط عند:

- [x] الكود مكتوب وفق **PSR-12**.
- [x] **Migration** مرفقة (إذا تتطلب DB).
- [x] **Service/Action class** مع PHPDoc.
- [x] **Feature Test** أو **Unit Test** للـ Business Logic.
- [x] تم **تشغيل المسار يدوياً** من الواجهة.
- [x] **Audit log** يسجل العملية (إذا حساسة).
- [x] **علامة ✅** في `07_PROGRESS.md` مع تاريخ الإنجاز.
- [x] **Commit** على Git مع رسالة واضحة (e.g. `feat: T-045 Add HMAC verification middleware`).

---

## 10. خطوات التحقق

### بعد كل Sprint:
1. تشغيل السيناريو الكامل من UI (E2E يدوي).
2. `php artisan test` (كل الاختبارات تمر).
3. Code Review ذاتي للـ Diff قبل الـ Commit.
4. تحديث `07_PROGRESS.md`.

### بعد كل Phase:
1. اختبار شامل للمسارات الحرجة:
   - Login → Dashboard → View Wallet.
   - Webhook → Points Awarded → Tier Upgrade.
   - Redemption Request → Admin Approve → Notification.
2. Performance Check: Page < 2s، API < 300ms.
3. Security Smoke Test: SQL Injection، XSS، CSRF.

### قبل الإطلاق (Phase 5):
- Load Testing (k6 - 500 وكيل متزامن).
- Security Audit (OWASP ZAP + Manual Pen Test).
- Database Backup Test.
- Rollback Plan موثّق.

---

## 11. الملاحظات الإدارية

### الأسئلة المفتوحة (من PRD §9)
| # | السؤال | الحالة |
|---|--------|--------|
| 1 | هل Main Site يدعم Webhooks حالياً؟ | ⏳ في انتظار |
| 2 | Provider الـ SMS (إن فُعّل)؟ | ⏳ في انتظار |
| 3 | قناة التحويل النقدي (بنك/PayPal/Wise)؟ | ⏳ في انتظار |
| 4 | متطلبات KYC/AML؟ | ⏳ في انتظار |
| 5 | تقارير ضريبية؟ | ⏳ في انتظار |
| 6 | عدد البيئات؟ | ⏳ في انتظار |
| 7 | استراتيجية النسخ الاحتياطي؟ | ⏳ في انتظار |

### القرارات المعتمدة
- ✅ Laravel 12 (بدلاً من 11 المقترحة في PRD).
- ✅ بدون Queue (Synchronous + Scheduler).
- ✅ بدون CI/CD (إعداد يدوي).
- ✅ بدون Monitoring (Sentry/Grafana/Prometheus).
- ✅ Cache: File (بدلاً من Redis).
- ✅ Admin Panel: من الصفر (بدون Filament).
- ✅ Design: مستوحى من fly29.net.

---

**📌 ملف التتبع:** [`07_PROGRESS.md`](./07_PROGRESS.md)
**📋 المهام التفصيلية:** [`08_TASKS_BACKLOG.md`](./08_TASKS_BACKLOG.md)

---

**نهاية خطة التطوير**
