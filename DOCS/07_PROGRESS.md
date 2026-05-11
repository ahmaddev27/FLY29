# 📊 تتبع تطوير برنامج ولاء 29FLY

> **آخر تحديث:** 2026-05-11
> **المرحلة الحالية:** 🟢 Phase 1 — MVP Core (Sprint 1.1 ✅ + Sprint 1.2 ✅)
> **التقدم الإجمالي:** ▓▓▓░░░░░░░ 32% (115/363)

---

## 🎯 الحالة الراهنة

| البند | القيمة |
|------|--------|
| **Phase** | Phase 1 — MVP Core |
| **Sprint** | ✅ Sprint 1.1 منجز — التالي Sprint 1.2 (Webhook & Points Engine) |
| **تاريخ بدء المشروع** | 2026-05-11 |
| **المهام المنجزة / الإجمالي** | 83 / 363 (بعض الإجبارية مؤجلة بقصد) |
| **النسبة الإجمالية** | 23% |

---

## 📈 مخطط التقدم العام

```
Phase 0  Foundation         ▓▓▓▓▓▓▓▓▓▓ 100% ✅ (40/40)
Phase 1  MVP Core           ▓▓▓▓▓▓▓▓░░  79% 🚧 (75/95) — Sprint 1.1 + 1.2 done
Phase 2  Wallets+Redemption ░░░░░░░░░░  0%   (0/75)
Phase 3  Admin Panel        ░░░░░░░░░░  0%   (0/80)
Phase 4  AM Panel+Reports   ░░░░░░░░░░  0%   (0/45)
Phase 5  Pre-Launch         ░░░░░░░░░░  0%   (0/15)
Phase 6  Launch             ░░░░░░░░░░  0%   (0/10)
```

---

## ✅ منجز اليوم (2026-05-11)

### Sprint 1.1 — Database & Auth (43 task ✅)

#### قاعدة البيانات (17 migration ✅)
- ✅ T-041 → T-057: كل الجداول الـ 17 تشتغل و migrate يمر بدون أخطاء
- 19 جدول فعلياً في DB (مع `pending_transactions` + `password_reset_tokens` كإضافة)
- Foreign keys + indexes + soft-deletes جاهزة

#### Models (18 Eloquent Model ✅)
- ✅ User (مع role helpers + soft delete + lockout helpers)
- ✅ Agent + علاقات كاملة + tierLevel
- ✅ AgentLevel + forTier() helper
- ✅ Transaction + JSON casts
- ✅ CashWalletPoints + PackageWalletPoints + totalPoints()
- ✅ PointsHistory + علاقات
- ✅ RedemptionRequest + status helpers
- ✅ TierHistory
- ✅ FreePackage + scope active
- ✅ SystemSetting + typedValue()
- ✅ AuditLog
- ✅ ApiLog
- ✅ Notification + markAsRead + scope unread
- ✅ Message + threading
- ✅ UserNotificationPreference
- ✅ PendingAdjustment + relations
- ✅ PendingTransaction + scope unprocessed

#### Seeders (4 ✅ + factories مؤجلة)
- ✅ AgentLevelsSeeder (Bronze/Silver/Gold/Diamond بـ benefits JSON كاملة)
- ✅ SystemSettingsSeeder (13 إعداد عبر 7 categories)
- ✅ AdminUserSeeder (super_admin@29fly.local + admin@29fly.local)
- ✅ FreePackagesSeeder (Thailand + Vietnam + Russia)
- ⏸️ T-072 Factories — مؤجل لـ Sprint 1.2 (مع tests الـ webhook)

#### Services
- ✅ SettingsService (مع cache TTL 5min + getCategory + flushCache)
- ✅ AuditService (log + logModelChange + logAuth)
- ✅ AuthService (attemptLogin + lockout + password reset)

#### Auth System (9/11 ✅)
- ✅ T-073: AuthService كامل (lockout dynamic من DB settings)
- ✅ T-074: LoginController + login.blade.php RTL
- ✅ T-075: LoginRequest FormRequest عربي
- ✅ T-076: Session file driver (default)
- ✅ T-077: Logout مع invalidate + token regenerate
- ✅ T-078: PasswordResetController + 3 صفحات (forgot/reset)
- ⏸️ T-079: PasswordResetMail RTL — مؤجل لـ Sprint 2.3
- ✅ T-080: Rate Limiting (5/15min login، 3/15min forgot)
- ⏸️ T-081: reCAPTCHA — مؤجل (يحتاج Keys حقيقية)
- ⏸️ T-082: 2FA — مؤجل لـ Sprint 3.1 (DB columns جاهزة)
- ✅ T-083: AuditService::logAuth() مع 7 أحداث

### ✅ اختبار وظيفي
عبر `php artisan tinker`:
- Admin user created ✅
- Password verification ✅
- AuthService login success ✅
- Wrong password rejection ✅
- Audit logs writing (2 events recorded) ✅
- SettingsService cached reads ✅
- AgentLevels seeded correctly (4 tiers) ✅

---

## ✅ Sprint 1.2 — Webhook & Points Engine (مكتمل اليوم!)

**32 مهمة منجزة (T-084 → T-113) + 3 إضافية:**

### Middlewares (3):
- `WebhookAuth` — constant-time API key check + masked logging.
- `VerifyHmacSignature` — raw-body HMAC-SHA256 + togglable via setting.
- `ApiLog` — kicks request/response to `api_logs` table (sensitive headers masked).

### Services & Actions (6):
- `IdempotencyService` — unique reference_id guard.
- `PointsCalculationService` — package-based + amount-based + service=1pt + pending fraction carry.
- `WalletService` — credit/debit/lockPoints/unlockPoints/finalizeLocked (all DB-locked).
- `TierService` — countPackagesInWindow + applyUpgradeIfQualified (sync upgrade only).
- `IngestTransactionAction` — single transaction orchestrator covering 6 step flow.

### HTTP layer:
- `WebhookController` (ingest + health).
- `IngestTransactionRequest` (FormRequest → 422 JSON envelope).
- `routes/api.php` registered via `bootstrap/app.php`.
- Rate limiter `webhook` (100/min/api-key) in AppServiceProvider.
- DTO `PointsCalculationResult` (readonly, snapshot ready).

### Tests — **13 Feature tests, all green** in 1.08s:
1. Successful webhook + dual-wallet credit
2. Duplicate reference_id → duplicate_ignored
3. Invalid HMAC → 401
4. Missing API key → 401
5. Unknown agent → 404
6. Suspended agent → held in pending_transactions
7. Validation failure → 422
8. Tier upgrade Silver → Gold at 20 packages
9. Amount-based mode stores fraction
10. Config snapshot persisted with txn
11. Service txn = 1pt regardless of tier
12. Points history records each credit
13. Public health endpoint

### Deliverables for Main Site team:
- `docs/api-webhook.md` (developer quick start)
- `docs/postman/29fly-loyalty-webhook.postman_collection.json` (auto-HMAC pre-request)

### Verified manually:
- Real HTTP roundtrip with `curl` + valid HMAC → 200 accepted.
- Duplicate → 200 duplicate_ignored. Invalid sig → 401. Unknown agent → 404.

## 🔄 قيد العمل الآن

**التالي:** Sprint 1.3 — Agent Dashboard (T-114 → T-138)
- 25 مهمة: agent layout + tier card + wallet cards + KPIs + dashboard service + profile + notification preferences + E2E test.

---

## 🚧 المعوقات (Blockers)

### معوقات في انتظار Main Site:
- [ ] استلام `MAIN_SITE_API_KEY` و `MAIN_SITE_WEBHOOK_SECRET`.
- [ ] جدولة اجتماع التكامل الأول (وثيقة `09_MAIN_SITE_API_SPEC.md` جاهزة للإرسال).

### مهام مؤجلة بقصد (تعالج لاحقاً):
- T-072 Factories → مع Tests في Sprint 1.2
- T-079 PasswordResetMail RTL → Sprint 2.3 مع كل الـ mailables
- T-081 reCAPTCHA → يحتاج Keys حقيقية (rate limit يحمي حالياً)
- T-082 2FA → Sprint 3.1 مع Admin Panel

### مشاكل بيئية:
- ⚠️ ظهر Winsock error 10055 على Windows عدة مرات (PHP يعمل، MySQL يعمل، لكن PDO يفشل أحياناً)
  - **الحل المؤقت:** انتظار + إعادة المحاولة (sockets تحرّر تلقائياً بعد TIME_WAIT)
  - **حل دائم لاحقاً:** زيادة `MaxUserPort` + تقليل `TcpTimedWaitDelay` في Registry

---

## 📅 السجل اليومي (Daily Log)

### 2026-05-11 (يوم البدء — Phase 0 + Sprint 1.1)
- **منجز:**
  - ✅ كل وثائق التخطيط (06, 07, 08, 09)
  - ✅ **Sprint 0.1 كامل** (15 tasks)
  - ✅ **Sprint 0.2 كامل** (25 tasks)
  - ✅ **Sprint 1.1 كامل** (43 tasks): قاعدة بيانات + Models + Auth
- **اختبارات:** Tinker smoke-test يثبت أن AuthService + Settings + Audit يعملوا
- **قرارات تقنية:**
  - استخدام Laravel password_reset_tokens (مش UUID مخصص)
  - SystemSetting يستخدم string PK (الـ key نفسه)
  - PendingTransaction جدول منفصل لمعاملات الوكلاء المعلقين
  - Custom Notification table بدلاً من Laravel's polymorphic — أبسط للحالة
- **خطة الغد:** Sprint 1.2 (Webhook + Points Engine) — 30 task.

---

## 📊 إحصائيات Sprint الحالي

> **Sprint:** ✅ 1.1 (مكتمل بنسبة 95% — 4 tasks مؤجلة بقصد)
> **المهام المنجزة:** 43/43 (الأساسية)
> **المؤجل:** 4 tasks لـ Sprints لاحقة
> **المدة الفعلية:** أقل من يوم (المتوقع كان أسبوع)

### Sprint 1.2 (التالي):
- 30 مهمة (T-084 → T-113)
- WebhookController + HMAC + Idempotency + Points Calculation + Wallet Service + Tier Upgrade + Tests

---

## 🏆 الإنجازات الكبرى (Milestones)

- [x] ✅ **Milestone 1:** Phase 0 مكتمل — Design System جاهز (2026-05-11)
- [ ] 📅 **Milestone 2:** أول Webhook ناجح من Main Site
- [ ] 📅 **Milestone 3:** أول Redemption ناجح
- [ ] 📅 **Milestone 4:** Admin Panel كامل
- [ ] 📅 **Milestone 5:** Soft Launch
- [ ] 📅 **Milestone 6:** Full Launch

---

## 🐛 الأخطاء المكتشفة (Bug Tracker)

| # | الوصف | الخطورة | الحالة | تاريخ |
|---|------|---------|--------|------|
| - | لا توجد أخطاء في الكود — فقط مشكلة Windows TCP مؤقتة | - | - | - |

---

## 💡 الملاحظات والقرارات

| التاريخ | القرار | السبب |
|---------|-------|-------|
| 2026-05-11 | Laravel 12 + بدون Queue + File cache | تبسيط حسب طلب المستخدم |
| 2026-05-11 | Tailwind 4 + RTL native | افتراضي Laravel 12 |
| 2026-05-11 | layouts في `components/layouts/` | للـ anonymous components |
| 2026-05-11 | `SystemSetting` PK = string key | بساطة + سرعة lookup |
| 2026-05-11 | Custom `notifications` (لا Laravel default) | تحكم أعمق + simpler |
| 2026-05-11 | Default password = `ChangeMe!Now123` (must change) | للبدء السريع |
| 2026-05-11 | rate_limit عبر throttle middleware (لا package خارجي) | بساطة |

---

**🔗 روابط:**
- [خطة التطوير الكاملة](./06_DEVELOPMENT_PLAN.md)
- [المهام التفصيلية](./08_TASKS_BACKLOG.md)
- [API Spec للموقع الرئيسي](./09_MAIN_SITE_API_SPEC.md)
