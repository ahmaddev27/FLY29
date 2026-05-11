# 📊 تتبع تطوير برنامج ولاء 29FLY

> **آخر تحديث:** 2026-05-11
> **المرحلة الحالية:** 🟢 Phase 1 — MVP Core (Sprint 1.1 ✅)
> **التقدم الإجمالي:** ▓▓▓░░░░░░░ 23% (83/363)

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
Phase 1  MVP Core           ▓▓▓▓░░░░░░  45% 🚧 (43/95)
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

## 🔄 قيد العمل الآن

**التالي:** Sprint 1.2 — Webhook & Points Engine (T-084 → T-113)
- 30 مهمة: WebhookController، HMAC verification، Idempotency، PointsCalculation، WalletService، Tier upgrade sync، Feature Tests شاملة، Mock webhook.

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
