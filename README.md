# 29FLY Loyalty Program

> برنامج ولاء وكلاء سفر **29FLY** — منصة ويب متكاملة تربط مبيعات الوكلاء من الموقع الرئيسي بنظام تصنيف ونقاط ومحفظتين مستقلتين.

---

## 📊 Live Progress Dashboard

> **آخر تحديث:** 2026-05-11

| الحقل | القيمة |
|------|--------|
| **التقدم الإجمالي** | `▓▓▓▓░░░░░░` **39%** (143 / 363 task) |
| **المرحلة الحالية** | 🚧 Phase 1 — MVP Core (Phase 1 ✅ مكتمل) |
| **آخر Sprint منجز** | ✅ Sprint 1.3 — Agent Dashboard |
| **التالي** | ⏭️ Sprint 2.1 — Cash Redemption |
| **Feature Tests الخضراء** | ✅ 27 / 27 (1.14s) |
| **عدد الـ Commits** | 9 |

---

## 🚀 تقدّم المراحل

```
Phase 0  Foundation              ▓▓▓▓▓▓▓▓▓▓ 100% ✅  (40/40)
Phase 1  MVP Core                ▓▓▓▓▓▓▓▓▓▓ 100% ✅  (103/103) ← Phase 1 مكتمل!
Phase 2  Wallets + Redemption    ░░░░░░░░░░   0% ⏸️  (0/75)
Phase 3  Admin Panel             ░░░░░░░░░░   0% ⏸️  (0/80)
Phase 4  AM Panel + Reports      ░░░░░░░░░░   0% ⏸️  (0/45)
Phase 5  Pre-Launch              ░░░░░░░░░░   0% ⏸️  (0/15)
Phase 6  Launch                  ░░░░░░░░░░   0% ⏸️  (0/10)
```

---

## ✅ آخر إنجاز: Sprint 1.3 — Agent Dashboard (28 task)

لوحة الوكيل **حية** وتعمل على `/agent/dashboard`:

| الفئة | المخرجات |
|------|--------|
| **Service** | `AgentDashboardService` يجمع: tier info، wallets، 4 KPIs، آخر 10 معاملات، أقرب باكج، warnings — في request واحد |
| **Controllers** | `DashboardController`، `ProfileController`، `NotificationPreferencesController`، `NotificationController` (AJAX) |
| **Middleware** | `EnsureAgent` (يحوّل admins/managers لوحاتهم تلقائياً) |
| **UI Components** | `<x-agent.tier-card>` (progress + countdown + colors)، `<x-agent.wallet-card>` (cash + package variants) |
| **Pages** | dashboard، profile، notification-preferences (مع 8 أنواع × 3 قنوات) |
| **Routes** | 16 route تحت `/agent/*` (مع placeholders للـ sprints القادمة) |
| **Tests** | 12 Feature tests للوحة الوكيل (auth + dashboard + profile + preferences) |
| **Bonus** | Resend mail integration للـ emails (Sprint 2.3) |

### ما يراه الوكيل لما يدخل ✅
- ✅ بطاقة تصنيفه الحالي (Bronze/Silver/Gold/Diamond) مع progress bar للترقية
- ✅ Countdown لإعادة التقييم
- ✅ بطاقتا المحفظتين (نقدية مع USD value + باكجات مع أقرب باكج)
- ✅ 4 KPI cards (نقاط/باكجات الشهر، القيمة الدولارية، أيام للتقييم)
- ✅ جدول آخر 10 معاملات
- ✅ Empty state للوكلاء الجدد
- ✅ Warning banner لخطر التخفيض
- ✅ Sidebar collapsible + Topbar مع notifications dropdown
- ✅ Profile page (edit name/phone/city + change password)
- ✅ Notification preferences (8 أنواع × 3 قنوات)

---

## 🏆 Milestones

- [x] ✅ **Milestone 1:** Phase 0 مكتمل — Design System جاهز *(2026-05-11)*
- [ ] 📅 **Milestone 2:** أول Webhook ناجح من Main Site *(يحتاج credentials فعلية)*
- [ ] 📅 **Milestone 3:** أول Redemption ناجح
- [ ] 📅 **Milestone 4:** Admin Panel كامل
- [ ] 📅 **Milestone 5:** Soft Launch
- [ ] 📅 **Milestone 6:** Full Launch

---

## 📜 تاريخ الـ Commits

```
NEW      feat: complete Sprint 1.3 — Agent Dashboard
401c94d  docs: turn README.md into live progress dashboard
cb4ee1a  feat: complete Sprint 1.2 — Webhook & Points Engine
782d42f  fix(ui): force spinner to paint before form submit navigates
b7f13a6  feat(ui): smart auto-loading on all buttons system-wide
b822274  feat: complete Sprint 1.1 — Database & Auth
986dbee  feat: complete Design System (Sprint 0.2)
19d7f8c  docs: add comprehensive Main Site API integration spec
c567483  chore: initial Laravel 12 project setup (Sprint 0.1)
```

---

## 🚧 Blockers الحالية

### معلّق على Main Site Team:
- [ ] استلام `MAIN_SITE_API_KEY` و `MAIN_SITE_WEBHOOK_SECRET` عبر قناة آمنة.
- [ ] جدولة اجتماع التكامل (وثيقة `09_MAIN_SITE_API_SPEC.md` جاهزة للإرسال).

### مهام مؤجلة بقصد:
| المهمة | تأجيل لـ | السبب |
|--------|---------|------|
| Mailable RTL templates | Sprint 2.3 | مع كل الـ notifications دفعة واحدة |
| reCAPTCHA v3 | لاحقاً | يحتاج Keys حقيقية — rate limit يحمي |
| 2FA للأدمن | Sprint 3.1 | مع بناء Admin Panel (DB جاهزة) |

---

## 🛠️ الستاك التقني

| الطبقة | التقنية |
|--------|--------|
| Backend | **Laravel 12** + PHP 8.4 |
| Database | MySQL 8 (utf8mb4_unicode_ci) |
| Frontend | Blade + Alpine.js 3 + **Tailwind CSS 4** (RTL native) |
| Queue | ❌ بدون — كل العمليات Synchronous |
| Cache | File (افتراضي Laravel) |
| Scheduler | Laravel Schedule (cron) |
| Tests | PHPUnit + sqlite in-memory |
| Auth | Session-based + custom lockout + (2FA لاحقاً) |

---

## 🎨 الديزاين سيستيم

مستوحى من [fly29.net](https://www.fly29.net):

| العنصر | اللون |
|--------|------|
| Primary | `#0066CC` |
| CTA | `#10B981` |
| 💎 Diamond | `#3B82F6` |
| 🥇 Gold | `#F59E0B` |
| 🥈 Silver | `#94A3B8` |
| 🥉 Bronze | `#A16207` |
| Font (AR) | Cairo |

**صفحة عرض المكوّنات الحيّة:** `/design-system` بعد تشغيل السيرفر.

---

## 🚀 إعداد البيئة

```bash
# 1. clone + dependencies
git clone <repo-url> Fly && cd Fly
composer install
npm install

# 2. تهيئة .env
cp .env.example .env
php artisan key:generate
# عدّل: DB_DATABASE=fly_loyalty + DB_USERNAME + DB_PASSWORD

# 3. قاعدة البيانات
mysql -u root -e "CREATE DATABASE fly_loyalty CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. migrations + seeders (يُنشئ admin افتراضي)
php artisan migrate --seed

# 5. تشغيل التطبيق
php artisan serve   # في terminal
npm run dev         # في terminal آخر (للـ Vite hot-reload)
```

**Default credentials (للتطوير فقط — بدّلها فوراً):**
```
super@29fly.local  /  ChangeMe!Now123
admin@29fly.local  /  ChangeMe!Now123
```

### اختبار الـ Webhook محلياً:
```bash
# في .env:
MAIN_SITE_API_KEY=test_key_dev_only_12345
MAIN_SITE_WEBHOOK_SECRET=test_secret_dev_only_67890

# تشغيل الـ tests:
php artisan test --filter=Webhook
```

---

## 📚 التوثيق الكامل

| الملف | الوصف |
|------|--------|
| [DOCS/01_USER_STORIES.md](DOCS/01_USER_STORIES.md) | 69 قصة مستخدم على 10 ملاحم |
| [DOCS/02_USE_CASES.md](DOCS/02_USE_CASES.md) | 15 حالة استخدام (UML) |
| [DOCS/03_PRD.md](DOCS/03_PRD.md) | متطلبات المنتج |
| [DOCS/04_SRS.md](DOCS/04_SRS.md) | مواصفات النظام (IEEE 830) |
| [DOCS/05_ARCHITECTURE_DIAGRAM.md](DOCS/05_ARCHITECTURE_DIAGRAM.md) | المخطط المعماري |
| [DOCS/06_DEVELOPMENT_PLAN.md](DOCS/06_DEVELOPMENT_PLAN.md) | خطة التطوير (14 أسبوع) |
| [**DOCS/07_PROGRESS.md**](DOCS/07_PROGRESS.md) | **السجل اليومي التفصيلي + decisions log** |
| [DOCS/08_TASKS_BACKLOG.md](DOCS/08_TASKS_BACKLOG.md) | 363 مهمة مفصّلة (T-001 → T-363) |
| [DOCS/09_MAIN_SITE_API_SPEC.md](DOCS/09_MAIN_SITE_API_SPEC.md) | مواصفات تكامل API (للطرف الآخر) |
| [docs/api-webhook.md](docs/api-webhook.md) | Quick start للمطوّرين |
| [docs/postman/](docs/postman/) | Postman collection |

---

## 📁 بنية المشروع

```
app/
├── Actions/              # IngestTransactionAction، orchestrators
├── DTOs/                 # PointsCalculationResult
├── Http/
│   ├── Controllers/      # Auth/، Agent/، Admin/، AccountManager/، Api/V1/
│   ├── Middleware/       # WebhookAuth، VerifyHmacSignature، ApiLog
│   └── Requests/
├── Models/               # 18 Eloquent model
├── Services/             # 7 services (Auth, Audit, Settings, Wallet, Tier, ...)
└── Notifications/        # (Sprint 2.3)

resources/views/
├── components/
│   ├── ui/               # 25 UI components (button, modal, tier-badge, ...)
│   ├── forms/
│   └── layout/
├── layouts/              # app, auth, agent, admin, manager
└── auth/                 # login, forgot-password, reset-password

database/
├── migrations/           # 17 migrations
├── seeders/              # 4 seeders
└── factories/            # User, Agent

tests/Feature/Webhook/    # 13 passing tests
```

---

## 📞 الفريق

- ** Developer:** Ahmad Jaber — ahmedjaberdev@gmail.com
- **Project Owner:** 29FLY Team

---

> **هذا المستودع سرّي — للاستخدام الداخلي فقط.**
> **آخر تحديث للـ Dashboard:** 2026-05-11 — Sprint 1.2 ✅
