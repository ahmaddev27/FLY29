# 29FLY Loyalty Program

> برنامج ولاء وكلاء سفر 29FLY — منصة ويب متكاملة تربط مبيعات الوكلاء من الموقع الرئيسي بنظام تصنيف ونقاط ومحفظتين مستقلتين.

---

## 📚 التوثيق

| الملف | الوصف |
|------|--------|
| [`DOCS/01_USER_STORIES.md`](DOCS/01_USER_STORIES.md) | 69 قصة مستخدم على 10 ملاحم |
| [`DOCS/02_USE_CASES.md`](DOCS/02_USE_CASES.md) | 15 حالة استخدام (UML) |
| [`DOCS/03_PRD.md`](DOCS/03_PRD.md) | متطلبات المنتج |
| [`DOCS/04_SRS.md`](DOCS/04_SRS.md) | مواصفات النظام (IEEE 830) |
| [`DOCS/05_ARCHITECTURE_DIAGRAM.md`](DOCS/05_ARCHITECTURE_DIAGRAM.md) | المخطط المعماري |
| [`DOCS/06_DEVELOPMENT_PLAN.md`](DOCS/06_DEVELOPMENT_PLAN.md) | خطة التطوير الكاملة (14 أسبوع) |
| [`DOCS/07_PROGRESS.md`](DOCS/07_PROGRESS.md) | تتبع التقدم اليومي |
| [`DOCS/08_TASKS_BACKLOG.md`](DOCS/08_TASKS_BACKLOG.md) | 363 مهمة تفصيلية |

---

## 🛠️ الستاك التقني

- **Backend:** Laravel 12 + PHP 8.4
- **Database:** MySQL 8
- **Frontend:** Blade + Alpine.js 3 + Tailwind CSS 4
- **Direction:** RTL (عربي)
- **Queue:** بدون — كل العمليات Synchronous
- **Cache:** File (افتراضي Laravel)
- **Scheduler:** Laravel Schedule (Cron)

---

## 🚀 إعداد البيئة

### المتطلبات
- PHP 8.3+ (مُختبر مع 8.4.5)
- Composer 2.x
- Node.js 20+
- MySQL 8.0
- Laragon (لـ Windows) أو ما يعادله

### التثبيت

```bash
# 1. clone المستودع
git clone <repo-url> Fly
cd Fly

# 2. تثبيت dependencies
composer install
npm install

# 3. إعداد .env
cp .env.example .env
php artisan key:generate

# 4. عدّل .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. إنشاء قاعدة البيانات
mysql -u root -e "CREATE DATABASE fly_loyalty CHARACTER SET utf8mb4;"

# 6. تشغيل migrations
php artisan migrate --seed

# 7. تشغيل dev server
php artisan serve
npm run dev
```

---

## 📁 بنية المشروع

```
app/
├── Actions/              # Single-action classes (orchestrators)
├── Console/Commands/     # Artisan Commands (Scheduler tasks)
├── DTOs/                 # Data Transfer Objects
├── Http/
│   ├── Controllers/
│   │   ├── Agent/
│   │   ├── Admin/
│   │   ├── AccountManager/
│   │   └── Api/V1/
│   └── Middleware/
├── Models/               # Eloquent Models
├── Notifications/        # Notifications (Sync — لا ShouldQueue)
├── Policies/             # Authorization Policies
└── Services/             # Business Logic Layer

resources/views/
├── layouts/              # app, agent, admin, manager
├── components/
│   ├── ui/               # Design System UI components
│   ├── forms/
│   └── layout/
├── auth/
├── agent/
├── admin/
├── manager/
└── emails/
```

---

## 🔌 التكامل مع Main Site

النظام يستقبل Webhook من fly29.net عند كل عملية بيع:

```http
POST /api/v1/transactions/ingest
Headers:
  X-API-Key: <secret>
  X-Signature: sha256=<HMAC-SHA256(body, webhook_secret)>
```

راجع [`DOCS/06_DEVELOPMENT_PLAN.md`](DOCS/06_DEVELOPMENT_PLAN.md) §5 للتفاصيل الكاملة.

---

## 🎨 الديزاين سيستيم

مستوحى من [fly29.net](https://www.fly29.net):
- **Primary:** `#0066CC` (أزرق fly29)
- **CTA:** `#10B981` (أخضر/تركواز)
- **Tiers:** Diamond `#3B82F6` / Gold `#F59E0B` / Silver `#94A3B8` / Bronze `#A16207`
- **Font (AR):** Cairo (Google Fonts)
- **RTL:** كامل

---

## 🧪 الاختبارات

```bash
php artisan test           # كل الاختبارات
php artisan test --filter=WebhookTest   # اختبار محدد
```

---

## 📅 الجدول الزمني

| المرحلة | المدة | الحالة |
|---------|------|--------|
| Phase 0: Foundation | أسبوع 1 | ⏳ قيد التنفيذ |
| Phase 1: MVP Core | أسابيع 2-4 | ⏸️ معلّق |
| Phase 2: Wallets + Redemption | أسابيع 5-7 | ⏸️ معلّق |
| Phase 3: Admin Panel | أسابيع 8-10 | ⏸️ معلّق |
| Phase 4: AM Panel + Reports | أسابيع 11-12 | ⏸️ معلّق |
| Phase 5: Pre-Launch | أسبوع 13 | ⏸️ معلّق |
| Phase 6: Launch | أسبوع 14 | ⏸️ معلّق |

---

## 📞 الفريق

- **Developer:** Ahmad Qaddora (aqaddora96@gmail.com)
- **Project Owner:** 29FLY Team

---

**التوثيق سري — للاستخدام الداخلي فقط.**
