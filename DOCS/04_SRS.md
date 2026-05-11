# مواصفات متطلبات النظام (SRS)
## برنامج ولاء وكلاء 29FLY

> **الإصدار:** 1.0
> **التاريخ:** نوفمبر 2026
> **المعيار:** IEEE 830-1998 (Recommended Practice for Software Requirements Specifications)
> **التصنيف:** سري — للاستخدام الداخلي

---

## 1. المقدمة (Introduction)

### 1.1 الغرض
تحدد هذه الوثيقة المتطلبات الوظيفية وغير الوظيفية لنظام برنامج ولاء وكلاء 29FLY، وتستهدف فريق الهندسة والاختبار والعمليات.

### 1.2 النطاق
النظام عبارة عن منصة ويب كاملة (Web Application) تُدمج مع الموقع الرئيسي لـ 29FLY، تتضمن:
- بوابة الوكلاء (Agent Portal).
- لوحة تحكم المدير (Admin Panel).
- لوحة مدير الحساب (Account Manager Panel).
- REST API لاستقبال البيانات من الموقع الرئيسي.
- نظام إشعارات متعدد القنوات.
- محرك تصنيف وتقييم آلي (Tier Engine).

### 1.3 التعريفات والمصطلحات

| المصطلح | التعريف |
|---------|---------|
| **Agent** | وكالة سفر مرتبطة بـ 29FLY |
| **Tier** | مستوى تصنيف الوكيل (Bronze/Silver/Gold/Diamond) |
| **Wallet** | محفظة نقاط (نقدية أو باكجات) |
| **Reference ID** | معرف فريد للمعاملة من الموقع الرئيسي |
| **Webhook** | إشعار HTTP تلقائي من الموقع الرئيسي |
| **Idempotency** | ضمان عدم تكرار تنفيذ نفس العملية |
| **Cron Job** | مهمة مجدولة تعمل تلقائيًا |

### 1.4 المراجع
- Loyalty Program Spec (وثيقة العميل الأصلية).
- User Stories Document (01_USER_STORIES.md).
- Use Cases Document (02_USE_CASES.md).
- PRD (03_PRD.md).
- IEEE 830-1998 Standard.

---

## 2. وصف عام للنظام (System Overview)

### 2.1 منظور النظام (System Perspective)
النظام **module مدمج** ضمن منظومة 29FLY الأشمل، يعتمد على:
- **الموقع الرئيسي:** كمصدر للمعاملات.
- **خدمات Email/SMS** الخارجية.
- **خدمة Object Storage** (S3-compatible).
- **خدمة Cache** (Redis).

### 2.2 وظائف النظام الرئيسية
1. **استقبال البيانات** من الموقع الرئيسي عبر API.
2. **احتساب النقاط** بطريقتين قابلتين للتبديل.
3. **إدارة التصنيفات** الآلية (Upgrade/Downgrade).
4. **معالجة الاستبدالات** (نقدي/باكج).
5. **توليد التقارير** والتصدير.
6. **إرسال الإشعارات** متعددة القنوات.

### 2.3 خصائص المستخدمين
- **Agent:** غير تقني، يحتاج واجهة بسيطة.
- **Account Manager:** متوسط التقنية، يحتاج أدوات إنتاجية.
- **Admin:** تقني/إداري، يحتاج تحكم شامل وتقارير عميقة.

### 2.4 القيود العامة
- PHP 8.3+ (لاستخدام مزايا حديثة).
- MySQL 8.0+ (للـ JSON columns و Window functions).
- HTTPS إجباري في كل البيئات.
- التوقيت Asia/Riyadh.

---

## 3. المتطلبات الوظيفية (Functional Requirements)

### 3.1 وحدة المصادقة (Authentication Module)

#### FR-AUTH-001 | تسجيل الدخول
**الوصف:** يجب أن يدعم النظام تسجيل دخول آمن للوكلاء، مديري الحسابات، والمدراء.
**المدخلات:** البريد الإلكتروني، كلمة المرور، reCAPTCHA token.
**المعالجة:**
- التحقق من reCAPTCHA (score ≥ 0.5).
- بحث المستخدم في `users` table.
- مقارنة كلمة المرور باستخدام `bcrypt::verify`.
- التحقق من `status = 'active'`.
- إنشاء Session (Laravel session driver: Redis).
**المخرجات:** Session cookie + JSON response مع التوجيه المناسب.

#### FR-AUTH-002 | المصادقة الثنائية (2FA)
**الوصف:** Admin و Super Admin يجب عليهم تفعيل 2FA.
**التقنية:** TOTP (Google Authenticator) عبر `google2fa-laravel` package.
**المخرجات:** QR code للإعداد الأولي، حقل OTP عند كل تسجيل دخول.

#### FR-AUTH-003 | استرجاع كلمة المرور
**التدفق:**
1. إدخال البريد الإلكتروني.
2. إنشاء `password_reset_token` (UUID v4, expires in 60 min).
3. إرسال رابط: `https://loyalty.29fly.com/reset-password?token=...`.
4. عند الفتح: نموذج كلمة مرور جديدة (≥ 8 أحرف، 1 حرف كبير، 1 رقم، 1 رمز).
5. تحديث `users.password` + إبطال جميع Sessions النشطة + إرسال إشعار بريدي.

#### FR-AUTH-004 | Rate Limiting
- 5 محاولات فاشلة خلال 15 دقيقة → حظر IP لمدة 15 دقيقة.
- تطبيق Laravel `RateLimiter::for('login', ...)`.

---

### 3.2 وحدة النقاط (Points Module)

#### FR-POINTS-001 | استقبال المعاملة (Webhook Ingestion)
**Endpoint:** `POST /api/v1/transactions/ingest`
**Headers المطلوبة:**
- `X-API-Key: <secret>`
- `X-Signature: sha256=<hmac>`
- `Content-Type: application/json`

**Body Schema:**
```json
{
  "agent_id": "string (required, max 50)",
  "transaction_type": "enum: package | service (required)",
  "amount_usd": "number (required, positive, max 2 decimals)",
  "destination": "string (optional, max 100)",
  "transaction_date": "ISO 8601 datetime (required)",
  "reference_id": "string (required, unique, max 100)"
}
```

**Response (نجاح):**
```json
{
  "status": "accepted",
  "transaction_id": "TXN-LOY-456789",
  "points_awarded": 4,
  "new_balance": { "cash": 1204, "package": 1204 }
}
```

**Response codes:**
- `200 OK` — نجاح أو duplicate
- `400 Bad Request` — Schema غير صالح
- `401 Unauthorized` — API Key أو Signature خاطئ
- `404 Not Found` — Agent غير موجود
- `422 Unprocessable Entity` — Agent معلق
- `429 Too Many Requests` — تجاوز Rate Limit (100/min/key)
- `500 Internal Server Error` — خطأ في النظام

#### FR-POINTS-002 | احتساب النقاط — Package-Based
**القاعدة:**
- إذا `transaction_type = 'package'`: نقاط = `tier.points_per_package` (مثلاً Gold = 4).
- إذا `transaction_type = 'service'`: نقاط = 1 (ثابت).
- يُضاف نفس العدد لكلتا المحفظتين.

#### FR-POINTS-003 | احتساب النقاط — Amount-Based
**القاعدة:**
- نقاط = `floor(amount_usd / tier.amount_per_point)`.
- مثال: Silver (1 لكل 250$), مبلغ 1000$ → 4 نقاط.
- يُحفظ الكسر في `pending_amount` للوكيل، يُجمع مع المعاملة القادمة.

#### FR-POINTS-004 | Config Snapshot **[تحسين مقترح]**
**القاعدة:**
- عند كل معاملة، يُحفظ snapshot للإعدادات النشطة في `points_history.config_snapshot` (JSON):
```json
{
  "calculation_method": "package_based",
  "tier_at_time": "gold",
  "points_per_package_at_time": 4,
  "point_value_usd_at_time": 2.0
}
```
- التقارير التاريخية تستخدم القيم المخزنة.

#### FR-POINTS-005 | Idempotency
- `transactions.reference_id` UNIQUE INDEX.
- محاولة إدراج reference_id مكرر → 200 OK مع `status: duplicate_ignored`.

#### FR-POINTS-006 | المحفظتان المستقلتان
- جدولان منفصلان: `cash_wallet_points`, `package_wallet_points`.
- كل عملية صرف تؤثر على محفظة واحدة فقط.
- DB Transactions تضمن الذرّية (atomicity).

#### FR-POINTS-007 | التعديل اليدوي
- نموذج Admin: المحفظة، الإضافة/الخصم، العدد، السبب.
- **[تحسين مقترح]** > 500 نقطة تتطلب Dual Approval (مدير ثانٍ).
- سجل كامل في `audit_log` و `points_history` (source=manual_adjustment).
- إشعار بريدي للوكيل (للإضافة).

---

### 3.3 وحدة التصنيف (Tier Module)

#### FR-TIER-001 | عتبات قابلة للتعديل
- جدول `agent_levels` يحتوي على:
  - `tier_name` (bronze/silver/gold/diamond)
  - `min_packages_monthly`
  - `points_per_package`
  - `amount_per_point` (للـ amount_based mode)
  - `benefits` (JSON)

#### FR-TIER-002 | الترقية الفورية (Synchronous)
- بعد كل معاملة (FR-POINTS-001)، يُستدعى `TierEvaluationService::checkUpgrade`.
- إذا الباكجات في النافذة ≥ العتبة الأعلى التالية → ترقية فورية.
- تحديث `agents.current_tier` و `agents.tier_valid_until = NOW() + 30 days`.
- سجل في `tier_history` (action=upgrade, from, to, reason).
- إشعار + Account Manager assignment (إذا لزم).

#### FR-TIER-003 | التخفيض الآلي (Cron)
- Job: `App\Jobs\EvaluateTiersJob`
- Schedule: يومي 02:00 Asia/Riyadh.
- المنطق: استعلام عن `agents WHERE tier_valid_until <= NOW()`.
- لكل وكيل: إعادة احتساب التصنيف المستحق، تطبيق التخفيض إذا لزم.
- Chunking: 100 وكيل/دفعة.

#### FR-TIER-004 | تحذير قبل التخفيض
- في نفس الـ Cron: استعلام عن `tier_valid_until BETWEEN NOW() AND NOW() + 7 days`.
- إذا الباكجات الحالية < العتبة → إرسال تحذير.

#### FR-TIER-005 | Rolling Window **[تحسين مقترح]**
- إعداد `tier_evaluation_mode`:
  - `calendar_month` (افتراضي): من 1 الشهر حتى الآن.
  - `rolling_30_days`: آخر 30 يومًا.
- التطبيق في `TierEvaluationService::countPackagesInWindow`.

---

### 3.4 وحدة الاستبدال (Redemption Module)

#### FR-REDEEM-001 | طلب تحويل نقدي
- Endpoint: `POST /api/agent/redemption/cash`
- Body: `{ "points": 800 }`
- Validation: ≥ `min_redemption_points` (من الإعدادات).
- DB Transaction:
  - حجز النقاط: `available_points -= 800`, `locked_points += 800`.
  - إنشاء `redemption_requests` (type=cash, status=pending).
- إشعار للأدمن.

#### FR-REDEEM-002 | الموافقة على التحويل
- Admin endpoint: `POST /api/admin/redemption/{id}/approve`.
- DB Transaction:
  - تحديث `status = 'approved'`, `approved_at = NOW()`, `approved_by = admin_id`.
  - خصم نهائي: `locked_points -= 800` (لا تُعاد للـ available).
- إشعار للوكيل.

#### FR-REDEEM-003 | رفض التحويل
- Body: `{ "reason": "..." }` (إجباري).
- DB Transaction:
  - تحديث `status = 'rejected'`, `rejection_reason`.
  - فك الحجز: `available_points += 800`, `locked_points -= 800`.
- إشعار للوكيل مع السبب.

#### FR-REDEEM-004 | استبدال باكج
- Endpoint: `POST /api/agent/redemption/package`
- Body: `{ "package_id": 5 }`
- Validation: الباكج نشط + رصيد كافٍ.
- DB Transaction:
  - خصم النقاط نهائيًا من `package_wallet_points.available_points`.
  - إنشاء `redemption_requests` (type=package, status=approved, fulfilled=false).
- إشعار للأدمن للتنفيذ اللوجستي.

#### FR-REDEEM-005 | إدارة الباكجات
- Admin CRUD على `free_packages`.
- حقول: name, destination, points_required, duration_days, description, image_url, valid_until, is_active.

---

### 3.5 وحدة الإشعارات (Notifications Module)

#### FR-NOTIF-001 | الإشعارات المطلوبة

| الحدث | المستلم | القنوات |
|------|--------|---------|
| Tier Upgrade | Agent | Email + In-app |
| Tier Downgrade | Agent | Email + In-app |
| Tier Warning (7 days) | Agent | Email + In-app |
| Points Earned | Agent | In-app only |
| Redemption Approved/Rejected | Agent | Email + In-app |
| Free Package Threshold Reached | Agent | Email + In-app |
| Manual Points Added | Agent | Email |
| New Redemption Request | Admin | Email + In-app |

#### FR-NOTIF-002 | التنفيذ
- Laravel Notifications class لكل نوع.
- Queue (Redis) لمنع تأخير الـ requests.
- Mailable templates بـ Blade مع RTL support.

#### FR-NOTIF-003 | تفضيلات المستخدم
- جدول `user_notification_preferences`:
  - `user_id`, `notification_type`, `email_enabled`, `sms_enabled`, `in_app_enabled`.
- صفحة إعدادات للوكيل تعدّل هذه القيم.

---

### 3.6 وحدة التقارير (Reports Module)

#### FR-REPORT-001 | التقارير المتاحة
1. **تقرير النقاط:** الموزعة/المستهلكة/المعلقة (بفلتر فترة).
2. **تقرير المبيعات:** حسب الوكيل/التصنيف/الفترة/الوجهة.
3. **تقرير التصنيفات:** عدد الوكلاء في كل مستوى + حركة Upgrade/Downgrade.
4. **تقرير الاستبدالات:** النقدية + الباكجات (بفلتر فترة).
5. **Top Agents:** أكثر الوكلاء مبيعًا/نقاطًا (10/50/100).

#### FR-REPORT-002 | التصدير
- صيغ مدعومة: Excel (.xlsx) عبر `PhpSpreadsheet` / `Laravel Excel`، PDF عبر `DomPDF`.
- **≤ 1000 صف:** تصدير فوري وتنزيل مباشر.
- **> 1000 صف:** Job في Queue + رابط بريدي صالح 24 ساعة.

---

### 3.7 وحدة الإعدادات (Settings Module)

#### FR-SETTINGS-001 | الإعدادات الديناميكية
جدول `system_settings` (Key-Value Store):

| Key | Default Value | Type | Description |
|-----|--------------|------|-------------|
| `calculation_method` | `package_based` | enum | طريقة احتساب النقاط |
| `point_value_usd` | `2.0` | float | قيمة النقطة بالدولار |
| `min_redemption_points` | `800` | int | الحد الأدنى للتحويل النقدي |
| `tier_evaluation_mode` | `calendar_month` | enum | calendar_month / rolling_30_days |
| `dual_approval_threshold` | `500` | int | عتبة Dual Approval للتعديلات اليدوية |
| `webhook_signature_verification` | `true` | bool | تفعيل التحقق من HMAC |
| `default_tier_for_new_agent` | `bronze` | enum | التصنيف الابتدائي |

#### FR-SETTINGS-002 | Cache Invalidation
- عند حفظ إعداد: `Cache::forget('system_settings:*')`.
- جميع القراءات تمر عبر `SettingsService::get($key)` مع Cache (TTL: 5 minutes).

---

## 4. المتطلبات غير الوظيفية (Non-Functional Requirements)

### 4.1 الأداء (Performance)

| المؤشر | الهدف |
|--------|-------|
| Page Load Time (Dashboard) | < 2 ثانية (p95) |
| API Response Time (Webhook) | < 300ms (p95) |
| API Throughput | ≥ 100 req/sec |
| Concurrent Users | 500 وكيل متزامن |
| Database Query Time | < 100ms (p95) |
| Cron Job Duration (Tier Eval) | < 30 دقيقة لـ 10,000 وكيل |

### 4.2 الموثوقية (Reliability)

| المؤشر | الهدف |
|--------|-------|
| Uptime | ≥ 99.5% (= ≤ 3.65 ساعة downtime/شهر) |
| Webhook Success Rate | ≥ 99.9% |
| Mean Time To Recovery (MTTR) | < 30 دقيقة |
| Data Loss Tolerance | صفر (RPO = 0 للمعاملات) |

### 4.3 الأمان (Security)

#### NFR-SEC-001 | تشفير
- HTTPS/TLS 1.3 إجباري.
- bcrypt cost ≥ 12 لكلمات المرور.
- تشفير البيانات الحساسة في DB (Laravel Crypt).

#### NFR-SEC-002 | حماية OWASP Top 10
- **SQL Injection:** Eloquent ORM + Prepared Statements حصرًا.
- **XSS:** Escape كل user input في Blade (`{{ }}` افتراضيًا).
- **CSRF:** Token على كل POST/PUT/DELETE forms.
- **Authentication:** bcrypt + 2FA للأدمن.
- **Session Management:** Redis + HTTPOnly + Secure flags + SameSite=Lax.

#### NFR-SEC-003 | API Security
- API Key لكل client (الموقع الرئيسي).
- HMAC-SHA256 signature على Webhooks.
- Rate Limiting: 100 req/min/key.
- IP Whitelist اختياري.
- Audit log لكل API call.

#### NFR-SEC-004 | Audit Trail
- جدول `audit_logs` يسجل:
  - `user_id`, `action`, `entity_type`, `entity_id`, `old_values` (JSON), `new_values` (JSON), `ip_address`, `user_agent`, `created_at`.
- العمليات المسجلة: تعديل نقاط، تغيير إعدادات، تعليق حساب، موافقة طلب، حذف.

### 4.4 قابلية التوسع (Scalability)
- Horizontal scaling عبر Load Balancer (Nginx upstream).
- Stateless application servers (Sessions في Redis).
- Database: Read Replicas للقراءة (المرحلة الثانية).
- Queue workers قابلة للتوسع المستقل عبر Horizon.

### 4.5 سهولة الصيانة (Maintainability)
- اتباع PSR-12 coding standards.
- PHPStan Level 7+ للـ static analysis.
- اختبارات: Unit tests ≥ 70%، Feature tests ≥ 50%.
- Documentation: PHPDoc لكل public method.
- CHANGELOG.md محدّث.

### 4.6 سهولة الاستخدام (Usability)
- WCAG 2.1 Level AA.
- RTL support كامل.
- Mobile responsive (320px - 4K).
- Onboarding tour للمستخدمين الجدد.
- Loading states واضحة.

### 4.7 التوافق (Compatibility)
- المتصفحات: Chrome 100+, Safari 15+, Edge 100+, Firefox 100+.
- OS: Windows 10+, macOS 12+, iOS 15+, Android 10+.

---

## 5. نموذج البيانات (Data Model)

### 5.1 ERD المختصر
انظر `05_ARCHITECTURE_DIAGRAM.md` للمخطط الكامل.

### 5.2 الجداول الرئيسية

#### users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    role ENUM('agent', 'account_manager', 'admin', 'super_admin') NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    status ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
    two_factor_secret VARCHAR(255) NULL,
    failed_login_attempts INT DEFAULT 0,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### agents
```sql
CREATE TABLE agents (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    external_agent_id VARCHAR(50) UNIQUE NOT NULL COMMENT 'ID من الموقع الرئيسي',
    business_name VARCHAR(255) NOT NULL,
    license_number VARCHAR(100) UNIQUE NOT NULL,
    country VARCHAR(100) NOT NULL,
    city VARCHAR(100),
    current_tier ENUM('bronze', 'silver', 'gold', 'diamond') DEFAULT 'bronze',
    tier_valid_until TIMESTAMP NULL,
    account_manager_id BIGINT UNSIGNED NULL,
    pending_amount DECIMAL(10, 2) DEFAULT 0 COMMENT 'كسور amount_based',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_manager_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_external_id (external_agent_id),
    INDEX idx_tier (current_tier),
    INDEX idx_tier_valid (tier_valid_until)
) ENGINE=InnoDB;
```

#### agent_levels
```sql
CREATE TABLE agent_levels (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tier_name ENUM('bronze', 'silver', 'gold', 'diamond') UNIQUE NOT NULL,
    min_packages_monthly INT NOT NULL,
    points_per_package INT NOT NULL,
    amount_per_point DECIMAL(10, 2) NOT NULL COMMENT 'للـ amount_based mode',
    benefits JSON,
    display_order INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed data:
INSERT INTO agent_levels VALUES
(1, 'bronze',  0,  2, 400, '{"manager":"shared"}',   1, NOW(), NOW()),
(2, 'silver',  10, 3, 300, '{"manager":"per_8"}',    2, NOW(), NOW()),
(3, 'gold',    20, 4, 250, '{"manager":"per_3","support":"priority","annual_meeting":1}', 3, NOW(), NOW()),
(4, 'diamond', 30, 5, 200, '{"manager":"dedicated","support":"urgent","annual_meeting":2}', 4, NOW(), NOW());
```

#### transactions
```sql
CREATE TABLE transactions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    agent_id BIGINT UNSIGNED NOT NULL,
    reference_id VARCHAR(100) UNIQUE NOT NULL COMMENT 'Idempotency key',
    transaction_type ENUM('package', 'service') NOT NULL,
    amount_usd DECIMAL(10, 2) NOT NULL,
    destination VARCHAR(100),
    points_awarded INT NOT NULL,
    config_snapshot JSON NOT NULL COMMENT 'الإعدادات النشطة لحظة المعاملة',
    transaction_date TIMESTAMP NOT NULL COMMENT 'وقت المعاملة الأصلي',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(id),
    INDEX idx_agent_date (agent_id, transaction_date),
    INDEX idx_type (transaction_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;
```

#### cash_wallet_points & package_wallet_points
```sql
CREATE TABLE cash_wallet_points (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    agent_id BIGINT UNSIGNED UNIQUE NOT NULL,
    available_points INT NOT NULL DEFAULT 0,
    locked_points INT NOT NULL DEFAULT 0 COMMENT 'محجوز لطلبات pending',
    lifetime_earned INT NOT NULL DEFAULT 0,
    lifetime_redeemed INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- نفس الهيكل لـ package_wallet_points
```

#### points_history
```sql
CREATE TABLE points_history (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    agent_id BIGINT UNSIGNED NOT NULL,
    wallet_type ENUM('cash', 'package') NOT NULL,
    transaction_id BIGINT UNSIGNED NULL,
    redemption_id BIGINT UNSIGNED NULL,
    points_delta INT NOT NULL COMMENT 'موجب (إضافة) أو سالب (خصم)',
    balance_after INT NOT NULL,
    source ENUM('transaction', 'redemption', 'manual_adjustment', 'rejection_refund') NOT NULL,
    description TEXT,
    config_snapshot JSON,
    created_by BIGINT UNSIGNED NULL COMMENT 'admin_id للـ manual',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(id),
    FOREIGN KEY (transaction_id) REFERENCES transactions(id),
    INDEX idx_agent_wallet (agent_id, wallet_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;
```

#### redemption_requests
```sql
CREATE TABLE redemption_requests (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    agent_id BIGINT UNSIGNED NOT NULL,
    type ENUM('cash', 'package') NOT NULL,
    points INT NOT NULL,
    cash_value_usd DECIMAL(10, 2) NULL,
    package_id BIGINT UNSIGNED NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled', 'fulfilled') DEFAULT 'pending',
    rejection_reason TEXT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    processed_by BIGINT UNSIGNED NULL,
    fulfilled_at TIMESTAMP NULL,
    FOREIGN KEY (agent_id) REFERENCES agents(id),
    FOREIGN KEY (package_id) REFERENCES free_packages(id),
    FOREIGN KEY (processed_by) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_agent (agent_id)
) ENGINE=InnoDB;
```

#### tier_history
```sql
CREATE TABLE tier_history (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    agent_id BIGINT UNSIGNED NOT NULL,
    from_tier ENUM('bronze', 'silver', 'gold', 'diamond') NULL,
    to_tier ENUM('bronze', 'silver', 'gold', 'diamond') NOT NULL,
    action ENUM('upgrade', 'downgrade', 'manual', 'initial') NOT NULL,
    packages_at_time INT NOT NULL,
    valid_until TIMESTAMP NOT NULL,
    triggered_by ENUM('system', 'admin') DEFAULT 'system',
    admin_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(id),
    INDEX idx_agent (agent_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;
```

#### system_settings
```sql
CREATE TABLE system_settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT NOT NULL,
    value_type ENUM('string', 'int', 'float', 'bool', 'json') DEFAULT 'string',
    description TEXT,
    updated_by BIGINT UNSIGNED NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB;
```

#### audit_logs
```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL COMMENT 'مثل: update_settings, suspend_agent',
    entity_type VARCHAR(100) NOT NULL,
    entity_id VARCHAR(50) NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;
```

#### api_logs
```sql
CREATE TABLE api_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    method VARCHAR(10) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    request_headers JSON,
    request_body JSON,
    response_code INT,
    response_body JSON,
    api_key_used VARCHAR(50),
    ip_address VARCHAR(45),
    duration_ms INT,
    reference_id VARCHAR(100) NULL,
    status ENUM('success', 'duplicate_ignored', 'unauthorized', 'failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_endpoint (endpoint),
    INDEX idx_reference (reference_id),
    INDEX idx_created (created_at),
    INDEX idx_status (status)
) ENGINE=InnoDB;
```

### 5.3 الجداول المساعدة
- `free_packages` — الباكجات المتاحة للاستبدال.
- `notifications` — In-app notifications.
- `messages` — رسائل بين Account Manager و Agent.
- `user_notification_preferences` — تفضيلات الإشعارات.
- `pending_adjustments` — التعديلات اليدوية في انتظار Dual Approval.
- `failed_jobs` — قائمة الـ Jobs الفاشلة (Laravel standard).

---

## 6. واجهات النظام (System Interfaces)

### 6.1 واجهة المستخدم (UI)
- **Framework:** Blade + Alpine.js + Tailwind CSS 3.4.
- **Component Library:** Custom components مبنية على Tailwind UI principles.
- **Charts:** Chart.js للـ Dashboards.
- **Icons:** Heroicons.

### 6.2 واجهات API الداخلية

#### 6.2.1 Agent API
```
GET    /api/agent/me                    - بيانات الوكيل الحالي
GET    /api/agent/dashboard             - بيانات Dashboard
GET    /api/agent/transactions          - سجل المعاملات (paginated)
GET    /api/agent/wallets               - أرصدة المحفظتين
POST   /api/agent/redemption/cash       - طلب تحويل نقدي
POST   /api/agent/redemption/package    - استبدال باكج
GET    /api/agent/redemptions           - سجل الاستبدالات
DELETE /api/agent/redemptions/{id}      - إلغاء طلب pending
GET    /api/agent/messages              - الرسائل
POST   /api/agent/messages              - إرسال رسالة
GET    /api/agent/notifications         - الإشعارات
PATCH  /api/agent/notifications/{id}/read - تعليم كمقروء
PUT    /api/agent/profile               - تعديل البيانات
PUT    /api/agent/notification-preferences - تعديل تفضيلات الإشعارات
```

#### 6.2.2 Admin API
```
GET    /api/admin/dashboard             - KPIs
GET    /api/admin/agents                - قائمة الوكلاء (filter, paginate)
POST   /api/admin/agents                - إنشاء وكيل
POST   /api/admin/agents/import         - استيراد Excel
GET    /api/admin/agents/{id}           - ملف وكيل كامل
PUT    /api/admin/agents/{id}           - تعديل وكيل
POST   /api/admin/agents/{id}/suspend   - تعليق
POST   /api/admin/agents/{id}/restore   - إلغاء تعليق
POST   /api/admin/agents/{id}/adjust-points - تعديل نقاط يدوي
GET    /api/admin/redemptions/pending   - الطلبات المعلقة
POST   /api/admin/redemptions/{id}/approve - موافقة
POST   /api/admin/redemptions/{id}/reject  - رفض
GET    /api/admin/packages              - الباكجات
POST   /api/admin/packages              - إضافة باكج
PUT    /api/admin/packages/{id}         - تعديل
DELETE /api/admin/packages/{id}         - حذف
GET    /api/admin/settings              - الإعدادات الحالية
PUT    /api/admin/settings              - تحديث الإعدادات
GET    /api/admin/reports/{type}        - تقرير
POST   /api/admin/reports/{type}/export - تصدير
GET    /api/admin/audit-logs            - سجل التدقيق
GET    /api/admin/api-logs              - سجل الـ API
```

#### 6.2.3 Webhook API (للموقع الرئيسي)
```
POST   /api/v1/transactions/ingest      - استقبال معاملة
GET    /api/v1/health                   - health check
```

### 6.3 واجهات خارجية
- **SMTP:** PHPMailer أو Symfony Mailer (مدمج في Laravel).
- **SMS Provider** (إن فُعّل): Twilio / Vonage / محلي حسب الدولة.
- **Object Storage:** S3-compatible API.

---

## 7. متطلبات الانتشار (Deployment Requirements)

### 7.1 البيئات
- **Development:** Docker Compose محلي.
- **Staging:** نسخة من Production بـ test data.
- **Production:** Cluster بـ Load Balancer.

### 7.2 البنية التحتية المقترحة
- **Web Servers:** 2x Nginx + PHP-FPM (Auto Scaling).
- **Database:** MySQL 8.0 Primary + Replica (Phase 2).
- **Cache/Queue:** Redis 7 (Cluster mode للـ HA).
- **Storage:** S3-compatible (DigitalOcean Spaces or AWS S3).
- **Monitoring:** Sentry (errors) + Grafana + Prometheus (metrics).
- **CI/CD:** GitHub Actions أو GitLab CI.

### 7.3 النسخ الاحتياطي
- **Database:** Daily full backup + hourly incremental.
- **Files:** Continuous backup إلى S3.
- **Retention:** 30 يومًا للـ daily، 12 شهرًا للـ monthly snapshots.
- **Test Restoration:** شهريًا.

---

## 8. متطلبات الاختبار (Testing Requirements)

### 8.1 أنواع الاختبارات
- **Unit Tests:** PHPUnit، تغطية ≥ 70% للـ Services والـ Models.
- **Feature Tests:** Laravel HTTP tests للـ Endpoints الرئيسية.
- **Browser Tests:** Laravel Dusk للـ Flows الحرجة (Login, Redemption).
- **Load Tests:** k6 أو Apache JMeter (محاكاة 500 وكيل متزامن).
- **Security Tests:** OWASP ZAP automated scan + pen test يدوي قبل الإطلاق.

### 8.2 معايير الجودة (Acceptance Criteria)
- جميع الـ P0 user stories تمر باختباراتها.
- ≥ 99% من الـ webhooks المرسلة تُسجَّل بنجاح في اختبار 24 ساعة.
- لا توجد ثغرات OWASP Top 10 (Critical/High).
- Page Load < 2s في Chrome DevTools.

---

## 9. ملحقات (Appendices)

### A. مخطط حالة (State Diagram) - طلب الاستبدال
```
[CREATED] → pending
    ↓
    ├─→ approved (admin action) → fulfilled (completed)
    ├─→ rejected (admin action with reason)
    └─→ cancelled (agent action, only from pending)
```

### B. Cron Jobs المطلوبة
| Job | Schedule | Purpose |
|-----|----------|---------|
| `EvaluateTiersJob` | يومي 02:00 | إعادة تقييم التصنيفات + تنبيهات |
| `ReconciliationJob` | يومي 03:00 | مطابقة مع Main Site |
| `CleanupExpiredTokensJob` | يومي 04:00 | تنظيف tokens منتهية |
| `ArchiveOldLogsJob` | أسبوعي 05:00 الجمعة | أرشفة logs > 90 يومًا |

### C. Environment Variables
```env
APP_ENV=production
APP_KEY=base64:...
APP_URL=https://loyalty.29fly.com
DB_CONNECTION=mysql
DB_HOST=...
REDIS_HOST=...
QUEUE_CONNECTION=redis
MAIL_MAILER=smtp
MAIN_SITE_API_KEY=<secret>
MAIN_SITE_WEBHOOK_SECRET=<secret>
RECAPTCHA_SITE_KEY=...
RECAPTCHA_SECRET_KEY=...
```

---

**نهاية وثيقة SRS**
