# 📋 المهام التفصيلية - برنامج ولاء 29FLY

> **الإجمالي:** ~363 مهمة
> **التنسيق:** كل مهمة لها ID فريد (T-XXX) + checkbox + وصف موجز + ملف مرتبط
> **الاستخدام:** ضع ✅ في `[ ]` عند الإنجاز، وحدّث `07_PROGRESS.md`

---

## 🟢 Phase 0: التأسيس (Foundation) — أسبوع 1

### Sprint 0.1: إعداد المشروع (Project Setup) — 3 أيام

- [x] **T-001** — التأكد من PHP 8.3+ مثبت على Laragon. ✅ **PHP 8.4.5**
- [x] **T-002** — إنشاء مشروع Laravel 12. ✅ **Laravel 12.58.0**
- [x] **T-003** — تهيئة `.env`: APP_NAME, APP_TIMEZONE=Asia/Riyadh, DB credentials, AR locale, Sync queue. ✅
- [x] **T-004** — إنشاء قاعدة بيانات MySQL `fly_loyalty` (utf8mb4). ✅
- [x] **T-005** — تشغيل `php artisan migrate` للتأكد من الاتصال. ✅
- [x] **T-006** — تثبيت **Tailwind CSS 4** (أحدث من 3.4) + Vite. ✅
- [x] **T-007** — تثبيت Alpine.js + تسجيله في `app.js`. ✅
- [x] **T-008** — ~~tailwindcss-rtl plugin~~ → **RTL via Tailwind 4 native** (logical properties + `rtl:` variant). ✅
- [x] **T-009** — تخصيص theme في `resources/css/app.css` (Tailwind 4 CSS-first config). ✅
- [x] **T-010** — تحميل Cairo font من Google Fonts في `layouts/app.blade.php`. ✅
- [x] **T-011** — إنشاء `resources/views/layouts/app.blade.php` بـ `<html dir="rtl" lang="ar">`. ✅
- [x] **T-012** — إنشاء بنية المجلدات: Services, Actions, DTOs, Notifications, Policies, Controllers/{Agent,Admin,AccountManager,Api/V1}, views/{auth,agent,admin,manager,emails,components}. ✅
- [x] **T-013** — تهيئة Git على branch main + استبعاد `.claude/` من المتتبَّع. ✅
- [x] **T-014** — إنشاء `README.md` شامل بالستاك والإعداد والروابط للتوثيق. ✅
- [x] **T-015** — إعداد Scheduler في `routes/console.php` (Laravel 12 يستخدمه بدلاً من Kernel.php). ✅

### Sprint 0.2: الديزاين سيستيم (Design System) — 4 أيام

- [x] **T-016** — CSS variables للألوان (مع تصنيفات + ظلال + radii) في `app.css`. ✅
- [x] **T-017** — Typography (Cairo + Inter + helpers + focus-visible). ✅
- [x] **T-018** — `<x-ui.button>`: 6 variants × 3 sizes + loading + disabled + as-link. ✅
- [x] **T-019** — `<x-ui.input>`: types + icons start/end + error state. ✅
- [x] **T-020** — `<x-ui.select>` مع placeholder + selected + chevron. ✅
- [x] **T-021** — `<x-ui.textarea>` مع error state. ✅
- [x] **T-022** — `<x-ui.card>`: variants (with-header) + actions slot + padding/shadow options. ✅
- [x] **T-023** — `<x-ui.stat-card>` للـ KPIs مع 8 ألوان + trend (up/down/neutral). ✅
- [x] **T-024** — `<x-ui.badge>`: 6 variants + size + dot indicator. ✅
- [x] **T-025** — `<x-ui.tier-badge>` للتصنيفات الأربعة (Bronze/Silver/Gold/Diamond) مع أيقونات. ✅
- [x] **T-026** — `<x-ui.modal>` Alpine.js (4 sizes) + ESC close + footer slot + dispatch events. ✅
- [x] **T-027** — `<x-ui.alert>`: 4 variants + dismissible + title + icon. ✅
- [x] **T-028** — `<x-ui.table>` + `<x-ui.table-row>` + `<x-ui.table-cell>` مع RTL + hover. ✅
- [x] **T-029** — `<x-ui.pagination>` مع windowing + first/last + previous/next. ✅
- [x] **T-030** — `<x-layout.sidebar>` collapsible + nav items + active state + badges. ✅
- [x] **T-031** — `<x-layout.topbar>` مع breadcrumbs + notifications dropdown + user dropdown. ✅
- [x] **T-032** — `<x-ui.tabs>` + `<x-ui.tab-panel>` بـ Alpine.js. ✅
- [x] **T-033** — `<x-ui.tooltip>` مع 4 positions. ✅
- [x] **T-034** — `<x-ui.progress>` للتقدم نحو التصنيف (مع 8 ألوان + sizes). ✅
- [x] **T-035** — `<x-ui.empty-state>` مع icon + description + actions slot. ✅
- [x] **T-036** — `<x-ui.spinner>` (3 sizes × 3 colors). ✅
- [x] **T-037** — `<x-forms.form-group>` + `<x-forms.error>` مع label/required/hint. ✅
- [x] **T-038** — `<x-ui.confirm-modal>` helper مع form submission. ✅
- [x] **T-039** — ~~`<x-ui.notification-dropdown>`~~ → مدمج داخل `<x-layout.topbar>`. ✅
- [x] **T-040** — صفحة `/design-system` تعرض كل المكونات الـ 25 (Live Documentation). ✅

---

## 🟢 Phase 1: MVP الأساسي — أسابيع 2-4

### Sprint 1.1: Database & Auth — أسبوع

#### Migrations (15 مهمة)

- [x] **T-041** — Migration: `users` (+ `password_reset_tokens`) — role, status, 2FA, lockout, soft-deletes. ✅
- [x] **T-042** — Migration: `agents` (FK user/AM + internal_notes + pending_amount). ✅
- [x] **T-043** — Migration: `agent_levels` (4 tiers + benefits JSON). ✅
- [x] **T-044** — Migration: `transactions` (+ `pending_transactions` for suspended agents). ✅
- [x] **T-045** — Migration: `cash_wallet_points`. ✅
- [x] **T-046** — Migration: `package_wallet_points`. ✅
- [x] **T-047** — Migration: `points_history` (مع source enum موسّع: refunds/reversals). ✅
- [x] **T-048** — Migration: `redemption_requests`. ✅
- [x] **T-049** — Migration: `tier_history`. ✅
- [x] **T-050** — Migration: `free_packages`. ✅
- [x] **T-051** — Migration: `system_settings` (+ category + is_public). ✅
- [x] **T-052** — Migration: `audit_logs`. ✅
- [x] **T-053** — Migration: `api_logs` (+ rate_limited status). ✅
- [x] **T-054** — Migration: `notifications` (custom — مع title/body/action_url). ✅
- [x] **T-055** — Migration: `messages` (مع parent_id للـ threads). ✅
- [x] **T-056** — Migration: `user_notification_preferences`. ✅
- [x] **T-057** — Migration: `pending_adjustments`. ✅

#### Models (10 مهام)

- [x] **T-058** — `User` + علاقات (agent, managedAgents, auditLogs, notifications, messages) + role helpers (isAdmin/isAgent/...). ✅
- [x] **T-059** — `Agent` + كل العلاقات (user, accountManager, cashWallet, packageWallet, transactions, redemptions, tierHistory, pendingAdjustments, tierLevel). ✅
- [x] **T-060** — `AgentLevel` (JSON cast لـ benefits + helper `forTier()`). ✅
- [x] **T-061** — `Transaction` + علاقات + JSON cast للـ config_snapshot. ✅
- [x] **T-062** — `CashWalletPoints` + `PackageWalletPoints` (مع helper `totalPoints()`). ✅
- [x] **T-063** — `PointsHistory` + علاقات + JSON cast. ✅
- [x] **T-064** — `RedemptionRequest` + helpers (isPending, isApproved). ✅
- [x] **T-065** — `TierHistory` + علاقات. ✅
- [x] **T-066** — `SystemSetting` (custom primary key + `typedValue()` helper). ✅
- [x] **T-067** — `AuditLog`, `ApiLog`, `FreePackage` (مع scope active), `Notification` (مع markAsRead), `Message` (مع threading), `UserNotificationPreference`, `PendingAdjustment`, `PendingTransaction`. ✅

#### Seeders & Factories (5 مهام)

- [x] **T-068** — `AgentLevelsSeeder` (4 تصنيفات بالقيم الافتراضية + benefits JSON مفصّلة). ✅
- [x] **T-069** — `SystemSettingsSeeder` (13 إعداد عبر 7 categories: points/redemption/tier/webhook/auth...). ✅
- [x] **T-070** — `AdminUserSeeder` (super_admin + admin افتراضيين بكلمة مرور بدّلها فوراً). ✅
- [x] **T-071** — `FreePackagesSeeder` (Thailand 1000، Vietnam 1000، Russia 5000). ✅
- [ ] **T-072** — Factories لـ Agent، Transaction، PointsHistory (مؤجل لـ Sprint 1.2 مع الـ tests).

#### Auth System (10 مهام)

- [x] **T-073** — `AuthService` (attemptLogin مع lockout + DB-driven settings، logout، sendPasswordResetLink، resetPassword). ✅
- [x] **T-074** — `LoginController` (show/store/destroy + route-by-role) + `auth/login.blade.php` RTL. ✅
- [x] **T-075** — `LoginRequest` FormRequest مع validation عربي. ✅
- [x] **T-076** — Session driver: file (default Laravel). ✅
- [x] **T-077** — Logout يبطّل الـ session + يجدّد CSRF token. ✅
- [x] **T-078** — `PasswordResetController` + 3 صفحات (forgot، reset). ✅
- [ ] **T-079** — `PasswordResetMail` RTL → مؤجل لـ Sprint 2.3 (مع كل الـ mailables). يستخدم Laravel's default الآن.
- [x] **T-080** — Rate Limiting بـ Laravel throttle middleware (5/15min on login، 3/15min on forgot). ✅
- [ ] **T-081** — reCAPTCHA → مؤجل (يحتاج Keys حقيقية). الـ rate limit يحمي حالياً.
- [ ] **T-082** — 2FA → مؤجل لـ Sprint 3.1 (مع admin panel، لأنه إجباري للأدمن فقط). DB columns جاهزة (`two_factor_secret`, `two_factor_enabled`).
- [x] **T-083** — `AuditService::logAuth()` يسجّل: login_success, login_failed, login_blocked, account_locked, logout, password_reset_requested, password_reset_completed. ✅

### Sprint 1.2: Webhook & Points Engine — أسبوع

- [ ] **T-084** — `Api\V1\WebhookController` class.
- [ ] **T-085** — Route: `POST /api/v1/transactions/ingest` (في `routes/api.php`).
- [ ] **T-086** — Middleware `WebhookAuth`: التحقق من `X-API-Key` مع DB lookup (constant-time).
- [ ] **T-087** — Middleware `VerifyHmacSignature`: حساب HMAC-SHA256 ومقارنته.
- [ ] **T-088** — `IngestTransactionRequest` FormRequest (validation كاملة).
- [ ] **T-089** — `IdempotencyCheckService`: استعلام `reference_id` UNIQUE.
- [ ] **T-090** — `SettingsService`: قراءة من `system_settings` مع File Cache (TTL 5 دقائق).
- [ ] **T-091** — `PointsCalculationService::calculatePackageBased()` (Tier × type).
- [ ] **T-092** — `PointsCalculationService::calculateAmountBased()` (floor + pending_amount).
- [ ] **T-093** — Config Snapshot logic: تجميع JSON من الإعدادات النشطة لحظة المعاملة.
- [ ] **T-094** — `WalletService::credit($agentId, $wallet, $points, $source)` داخل DB Transaction.
- [ ] **T-095** — `WalletService::debit($agentId, $wallet, $points, $source)`.
- [ ] **T-096** — `WalletService::lockPoints($agentId, $wallet, $points)`.
- [ ] **T-097** — `WalletService::unlockPoints($agentId, $wallet, $points)`.
- [ ] **T-098** — `IngestTransactionAction` (orchestrator): يستدعي كل الخطوات بـ DB Transaction.
- [ ] **T-099** — `TierService::evaluateTier($agentId)`: حساب الباكجات في النافذة + مقارنة.
- [ ] **T-100** — `TierService::upgradeTier($agentId, $newTier)`: تحديث + tier_history + إشعار.
- [ ] **T-101** — Middleware `ApiLog`: تسجيل كل request/response في `api_logs`.
- [ ] **T-102** — Webhook response formatter (موحّد JSON).
- [ ] **T-103** — Error handling: 401/422/500 مع رسائل مناسبة.
- [ ] **T-104** — Rate Limiting على webhook: 100 req/min/key.
- [ ] **T-105** — Feature Test: webhook ناجح + verify points + verify wallet update.
- [ ] **T-106** — Feature Test: `reference_id` مكرر → `duplicate_ignored`.
- [ ] **T-107** — Feature Test: HMAC خاطئ → 401.
- [ ] **T-108** — Feature Test: Agent معلق → 422.
- [ ] **T-109** — Feature Test: Tier upgrade عند تخطي العتبة.
- [ ] **T-110** — Feature Test: amount_based يحفظ الكسر في `pending_amount`.
- [ ] **T-111** — Feature Test: config_snapshot يُخزّن بالصيغة الصحيحة.
- [ ] **T-112** — Postman Collection + Mock webhook لاختبار يدوي.
- [ ] **T-113** — توثيق `/docs/api-webhook.md` للمبرمج الخارجي.

### Sprint 1.3: Agent Dashboard — أسبوع

- [ ] **T-114** — Layout `resources/views/layouts/agent.blade.php`.
- [ ] **T-115** — Sidebar الوكيل: Dashboard, Wallets, Redemption, History, Messages, Profile.
- [ ] **T-116** — Topbar: notification icon (مع badge counter)، user dropdown، logout.
- [ ] **T-117** — تعديلات CSS RTL لـ Sidebar/Topbar.
- [ ] **T-118** — `Agent\DashboardController::index()`.
- [ ] **T-119** — `DashboardService::aggregateData($agentId)` يجمع: wallets, tier, KPIs, recent transactions.
- [ ] **T-120** — `<x-agent.tier-card>` مع Progress Bar + countdown timer.
- [ ] **T-121** — `<x-agent.wallet-card>` Cash (مع زر "تحويل لرصيد نقدي").
- [ ] **T-122** — `<x-agent.wallet-card>` Package (مع زر "استبدال بباكج").
- [ ] **T-123** — KPIs section: 4 بطاقات (نقاط الشهر، باكجات الشهر، رصيد الدولاري، أيام لإعادة التقييم).
- [ ] **T-124** — جدول آخر 10 معاملات على Dashboard.
- [ ] **T-125** — Tier countdown timer (JS بـ Alpine).
- [ ] **T-126** — Progress to next tier indicator (نسبة + باقي X باكج).
- [ ] **T-127** — Empty State للوكلاء الجدد بدون معاملات.
- [ ] **T-128** — Warning Banner أحمر إذا باقي < 7 أيام والباكجات < العتبة.
- [ ] **T-129** — حساب وعرض القيمة الدولارية (`points × point_value_usd`).
- [ ] **T-130** — حساب أقرب باكج مجاني يمكن استبداله.
- [ ] **T-131** — `Agent\ProfileController::show()` صفحة عرض البيانات.
- [ ] **T-132** — `Agent\ProfileController::update()` تعديل (مع منع تعديل البريد ورقم الترخيص).
- [ ] **T-133** — صفحة Notification Preferences للوكيل.
- [ ] **T-134** — تفعيل Notifications Dropdown (مع Mark as Read).
- [ ] **T-135** — Feature Test: Dashboard يحمّل البيانات بشكل صحيح.
- [ ] **T-136** — Feature Test: Tier upgrade ينعكس على Dashboard.
- [ ] **T-137** — اختبار يدوي E2E: Login → Dashboard → كل البطاقات.
- [ ] **T-138** — Performance: Dashboard load time < 2s (قياس).

---

## 🟢 Phase 2: المحفظتان والاستبدال — أسابيع 5-7

### Sprint 2.1: Cash Redemption — أسبوع

- [ ] **T-139** — صفحة "تحويل النقاط" `agent/redemption/cash.blade.php`.
- [ ] **T-140** — Slider + Number input بـ Alpine.js (مع حد أدنى/أقصى).
- [ ] **T-141** — Validation فورية: `>= min_redemption_points`.
- [ ] **T-142** — Validation: `<= available_points`.
- [ ] **T-143** — Confirmation Modal مع تفاصيل الطلب.
- [ ] **T-144** — `Agent\RedemptionController::storeCash()`.
- [ ] **T-145** — `RedemptionService::createCashRequest()` داخل DB Transaction.
- [ ] **T-146** — `WalletService::lockPoints()` (با re-check للرصيد).
- [ ] **T-147** — `NewRedemptionRequestNotification` للأدمن (email + in-app).
- [ ] **T-148** — صفحة "طلباتي" `agent/redemptions/index.blade.php`.
- [ ] **T-149** — `Agent\RedemptionController::destroy()` لإلغاء طلب pending.
- [ ] **T-150** — منطق الإلغاء: unlock points + تحديث status = cancelled.
- [ ] **T-151** — صفحة الأدمن "Pending Requests" `admin/redemptions/pending.blade.php`.
- [ ] **T-152** — `Admin\RedemptionController::approve($id)`.
- [ ] **T-153** — Approve flow: `locked_points -= X` (خصم نهائي، لا يعود للـ available).
- [ ] **T-154** — `Admin\RedemptionController::reject($id)`.
- [ ] **T-155** — Reject flow: unlock points (`available += X`، `locked -= X`).
- [ ] **T-156** — Validation: reject reason إجباري.
- [ ] **T-157** — `RedemptionApprovedNotification` (email + in-app).
- [ ] **T-158** — `RedemptionRejectedNotification` (email + in-app مع السبب).
- [ ] **T-159** — مكوّن `<x-ui.status-badge>` (Pending/Approved/Rejected/Cancelled/Fulfilled).
- [ ] **T-160** — Feature Test: Full Cash Redemption Flow.
- [ ] **T-161** — Feature Test: Race Condition prevention (محاولتان متزامنتان).
- [ ] **T-162** — Feature Test: Cancel pending request يعيد النقاط.
- [ ] **T-163** — Feature Test: Reject يتطلب سبب نصي.

### Sprint 2.2: Package Redemption — نصف أسبوع

- [ ] **T-164** — صفحة Admin Free Packages `admin/packages/index.blade.php`.
- [ ] **T-165** — `Admin\PackageController::create/store` + Form.
- [ ] **T-166** — `Admin\PackageController::edit/update`.
- [ ] **T-167** — Toggle is_active (إخفاء عن الوكلاء بدون حذف).
- [ ] **T-168** — رفع صورة الباكج (Laravel Storage).
- [ ] **T-169** — صفحة الوكيل "الباكجات المجانية" `agent/packages/index.blade.php`.
- [ ] **T-170** — Modal تفاصيل الباكج (الوجهة، المدة، النقاط).
- [ ] **T-171** — Confirmation Modal مع تحذير "سيتم خصم X نقطة".
- [ ] **T-172** — `Agent\RedemptionController::storePackage()`.
- [ ] **T-173** — Package redemption flow: `status = approved`، `fulfilled = false`، خصم مباشر.
- [ ] **T-174** — Notification للأدمن (logistics: التواصل مع الوكيل).
- [ ] **T-175** — Feature Test: Package Redemption Flow.
- [ ] **T-176** — Feature Test: رصيد غير كافٍ → خطأ مناسب.
- [ ] **T-177** — Feature Test: باكج غير نشط لا يظهر/يُحجب.
- [ ] **T-178** — Feature Test: التحقق من الرصيد عند التأكيد (race condition).

### Sprint 2.3: نظام الإشعارات — نصف أسبوع

- [ ] **T-179** — Base Notification class structure.
- [ ] **T-180** — `TierUpgradedNotification` + email template RTL.
- [ ] **T-181** — `TierDowngradedNotification` + email template RTL.
- [ ] **T-182** — `TierWarningNotification` + email template (تحذير 7 أيام).
- [ ] **T-183** — `PointsEarnedNotification` (in-app فقط).
- [ ] **T-184** — `RedemptionApprovedNotification` + email.
- [ ] **T-185** — `RedemptionRejectedNotification` + email (يشمل السبب).
- [ ] **T-186** — `NewRedemptionRequestNotification` للأدمن + email.
- [ ] **T-187** — `ManualPointsAddedNotification` للوكيل + email.
- [ ] **T-188** — `FreePackageThresholdReachedNotification` + email.
- [ ] **T-189** — UI تفضيلات الإشعارات للوكيل (checkbox لكل نوع/قناة).
- [ ] **T-190** — `NotificationService::dispatch($user, $notification)`: يقرأ التفضيلات + يرسل عبر القنوات النشطة.
- [ ] **T-191** — `try/catch` حول `Mail::send` مع logging الفشل في `failed_notifications`.
- [ ] **T-192** — تفعيل In-app dropdown (Alpine.js + AJAX).
- [ ] **T-193** — `Agent\NotificationController::markAsRead($id)`.
- [ ] **T-194** — `Agent\NotificationController::markAllAsRead()`.
- [ ] **T-195** — Badge counter للإشعارات غير المقروءة.
- [ ] **T-196** — قوالب البريد RTL مع شعار 29FLY.
- [ ] **T-197** — Feature Test: تفضيلات الإشعارات محترمة.
- [ ] **T-198** — Feature Test: فشل إرسال email لا يكسر الـ flow.

### Sprint 2.4: سجل المعاملات والتصدير — أسبوع

- [ ] **T-199** — صفحة سجل النقاط `agent/transactions/index.blade.php`.
- [ ] **T-200** — Filter: نطاق التاريخ (date range picker).
- [ ] **T-201** — Filter: نوع المعاملة (package/service).
- [ ] **T-202** — Filter: المحفظة (cash/package/all).
- [ ] **T-203** — بحث بـ reference_id.
- [ ] **T-204** — Pagination (50 صف لكل صفحة).
- [ ] **T-205** — Sort بالتاريخ والمبلغ والنقاط.
- [ ] **T-206** — Export CSV (Maatwebsite/Excel) synchronous.
- [ ] **T-207** — Export Excel (.xlsx).
- [ ] **T-208** — Export PDF (DomPDF) مع RTL.
- [ ] **T-209** — Streamed Response للملفات الكبيرة (>5000 صف): `Response::streamDownload`.
- [ ] **T-210** — Chunked DB reading (`->chunk(500)`) لمنع نفاد الذاكرة.
- [ ] **T-211** — Feature Test: الفلاتر تعمل بشكل صحيح.
- [ ] **T-212** — Feature Test: Pagination صحيحة.
- [ ] **T-213** — Feature Test: Export يولّد ملف صحيح بالـ headers الصحيحة.

---

## 🟢 Phase 3: لوحة الأدمن الكاملة — أسابيع 8-10

### Sprint 3.1: Admin Dashboard & Agents — أسبوع

- [ ] **T-214** — Layout `resources/views/layouts/admin.blade.php`.
- [ ] **T-215** — Admin Sidebar: Dashboard, Agents, Requests, Packages, Settings, Reports, AMs, Audit.
- [ ] **T-216** — `/admin/login` route منفصل + view مختلفة عن agent login.
- [ ] **T-217** — Middleware `Verify2FA` إجباري للأدمن (يجبر إعداد 2FA عند أول دخول).
- [ ] **T-218** — `Admin\DashboardController::index()`.
- [ ] **T-219** — `AdminDashboardService::aggregate()`: KPIs (إجمالي الوكلاء، النشطون، الموزعون حسب التصنيف، النقاط).
- [ ] **T-220** — Top 10 Agents Leaderboard (بأكثر مبيعات/نقاط).
- [ ] **T-221** — Pending Requests Quick Action panel.
- [ ] **T-222** — Sales Growth Chart (Chart.js - آخر 12 شهر).
- [ ] **T-223** — Agent Growth Chart.
- [ ] **T-224** — Tier Distribution Chart (Pie chart).
- [ ] **T-225** — `Admin\AgentController::index()` صفحة قائمة الوكلاء.
- [ ] **T-226** — Filters: tier, country, status, period.
- [ ] **T-227** — بحث: الاسم/البريد/رقم الترخيص.
- [ ] **T-228** — Bulk actions: تعليق، إلغاء تعليق، تصدير.
- [ ] **T-229** — `Admin\AgentController::show($id)` Agent Profile كامل.
- [ ] **T-230** — Tab: Transactions (للوكيل المحدد).
- [ ] **T-231** — Tab: Redemptions.
- [ ] **T-232** — Tab: Internal Notes (لا يراها الوكيل).
- [ ] **T-233** — `Admin\AgentController::create/store` نموذج إنشاء وكيل.
- [ ] **T-234** — تولید Password Setup Token (UUID، 24h).
- [ ] **T-235** — `AgentWelcomeMail` ترحيبي مع رابط تعيين كلمة المرور.
- [ ] **T-236** — صفحة Excel Import.
- [ ] **T-237** — تحميل قالب Excel فارغ.
- [ ] **T-238** — Validation لكل صف قبل الإدراج.
- [ ] **T-239** — تقرير أخطاء قابل للتنزيل (الصفوف الفاشلة).
- [ ] **T-240** — DB Transaction للاستيراد (Rollback عند فشل أي صف، أو Partial كخيار).
- [ ] **T-241** — `Admin\AgentController::suspend($id)` + إدخال السبب + audit log.
- [ ] **T-242** — `Admin\AgentController::unsuspend($id)` + audit log.
- [ ] **T-243** — Soft Delete + archive بعد 90 يوم.

### Sprint 3.2: Adjustments & Settings — أسبوع

- [ ] **T-244** — صفحة "تعديل نقاط" للأدمن (داخل Agent Profile).
- [ ] **T-245** — `AdjustPointsAction` class مع validation.
- [ ] **T-246** — Dual Approval logic: إذا points > `dual_approval_threshold` → save في `pending_adjustments`.
- [ ] **T-247** — `pending_adjustments` flow كامل.
- [ ] **T-248** — UI للأدمن الثاني للموافقة على pending adjustment.
- [ ] **T-249** — إشعار لمديرين آخرين عند وجود pending.
- [ ] **T-250** — `ManualPointsAddedNotification` للوكيل (للإضافة فقط).
- [ ] **T-251** — Audit log كامل لكل تعديل يدوي.
- [ ] **T-252** — صفحة Settings الديناميكية `admin/settings.blade.php`.
- [ ] **T-253** — حقل: `calculation_method` (toggle package_based / amount_based).
- [ ] **T-254** — حقل: `point_value_usd`.
- [ ] **T-255** — حقل: `min_redemption_points`.
- [ ] **T-256** — حقول: tier_thresholds (Diamond/Gold/Silver) كل واحد بصندوق.
- [ ] **T-257** — حقول: points_per_tier لكل من 4 تصنيفات.
- [ ] **T-258** — حقول: amount_per_point لكل تصنيف (mode amount_based).
- [ ] **T-259** — حقول: package_redemption_costs (Thailand, Russia...).
- [ ] **T-260** — Impact Analysis قبل الحفظ (عدد الوكلاء المتأثرين).
- [ ] **T-261** — Confirmation Modal مع تحذير "سيؤثر على X وكيل".
- [ ] **T-262** — `SettingsService` cache invalidation بعد الحفظ.
- [ ] **T-263** — صفحة Account Managers list.
- [ ] **T-264** — Create AM form.
- [ ] **T-265** — تعيين وكلاء يدوياً للـ AM.
- [ ] **T-266** — Toggle Auto-assign بالـ tier (Diamond → AM مخصص، Gold → AM/3 وكلاء، إلخ).
- [ ] **T-267** — Feature Test: Dual Approval flow كامل.
- [ ] **T-268** — Feature Test: Settings cache invalidation فعّال.

### Sprint 3.3: Requests Center & Audit — أسبوع

- [ ] **T-269** — صفحة Central Requests `admin/requests/index.blade.php`.
- [ ] **T-270** — Tabs: Pending / Approved / Rejected / All.
- [ ] **T-271** — Filters: النوع (cash/package)، الوكيل، الفترة.
- [ ] **T-272** — Bulk Approve action.
- [ ] **T-273** — Bulk Reject action (مع سبب واحد جماعي).
- [ ] **T-274** — Confirmation Modal للـ bulk actions.
- [ ] **T-275** — صفحة Audit Log View (Super Admin فقط).
- [ ] **T-276** — Filters: user/action/date/entity_type.
- [ ] **T-277** — بحث في القيم القديمة/الجديدة (JSON search).
- [ ] **T-278** — Pagination + Sort.
- [ ] **T-279** — صفحة API Logs.
- [ ] **T-280** — Filters: endpoint/status/date.
- [ ] **T-281** — صفحة Tier Management (عرض كل التصنيفات والحركات).
- [ ] **T-282** — Manual Tier Change (override من الأدمن).
- [ ] **T-283** — تسجيل manual override في `tier_history`.
- [ ] **T-284** — صفحة Announcements.
- [ ] **T-285** — نموذج إنشاء إعلان (subject, body HTML).
- [ ] **T-286** — استهداف: الكل / حسب التصنيف / حسب الدولة.
- [ ] **T-287** — إرسال الإعلان (email + in-app sync).
- [ ] **T-288** — صفحة الوكلاء المعلقين/المحذوفين.
- [ ] **T-289** — Restore agent خلال 90 يوم.
- [ ] **T-290** — Archive logic (> 90 يوم).
- [ ] **T-291** — Tier Threshold modification (مع تنبيه التأثير).
- [ ] **T-292** — Feature Test: Audit log يلتقط كل العمليات الحساسة.
- [ ] **T-293** — Feature Test: Super Admin فقط يرى Audit Log.

---

## 🟢 Phase 4: مدير الحساب والتقارير — أسابيع 11-12

### Sprint 4.1: Account Manager Panel — أسبوع

- [ ] **T-294** — Layout `resources/views/layouts/manager.blade.php`.
- [ ] **T-295** — Manager Sidebar: Dashboard, My Agents, Messages, Suggestions, Reports.
- [ ] **T-296** — `Manager\DashboardController::index()`.
- [ ] **T-297** — صفحة وكلاء المدير `manager/agents/index.blade.php`.
- [ ] **T-298** — Middleware Row-Level Security (يرى فقط وكلاءه).
- [ ] **T-299** — صفحة Agent Performance (مع إحصاءات).
- [ ] **T-300** — Sparkline charts لمبيعات كل وكيل.
- [ ] **T-301** — تنبيه أحمر على الوكلاء غير النشطين (>14 يوم).
- [ ] **T-302** — صفحة Messages Inbox للمدير.
- [ ] **T-303** — صفحة Messages Outbox.
- [ ] **T-304** — نموذج إرسال رسالة لوكيل.
- [ ] **T-305** — Reply to message.
- [ ] **T-306** — Notification (in-app + email) عند رسالة جديدة.
- [ ] **T-307** — نموذج Suggest Points Adjustment.
- [ ] **T-308** — Save كـ `pending_admin_approval`.
- [ ] **T-309** — إشعار للأدمن باقتراح جديد.
- [ ] **T-310** — صفحة Reports محدودة للـ Manager (وكلاؤه فقط).
- [ ] **T-311** — Feature Test: Manager يرى وكلاءه فقط.
- [ ] **T-312** — Feature Test: Messaging flow.
- [ ] **T-313** — Feature Test: Suggestion → Admin approval flow.

### Sprint 4.2: Reports & Reconciliation — أسبوع

- [ ] **T-314** — صفحة Reports Index `admin/reports/index.blade.php`.
- [ ] **T-315** — Points Report (الموزعة / المستهلكة / المعلقة).
- [ ] **T-316** — Filters للـ Points Report.
- [ ] **T-317** — Chart مرئي للـ Points Report.
- [ ] **T-318** — Sales Report (حسب الوكيل/التصنيف/الفترة/الوجهة).
- [ ] **T-319** — Chart للـ Sales Report.
- [ ] **T-320** — Tiers Report (توزيع + حركة Upgrade/Downgrade).
- [ ] **T-321** — Redemptions Report.
- [ ] **T-322** — Top Agents Report (10/50/100).
- [ ] **T-323** — Export Reports إلى PDF.
- [ ] **T-324** — Export Reports إلى Excel.
- [ ] **T-325** — Streamed Export للتقارير الكبيرة.
- [ ] **T-326** — `MainSiteApiService` class (HTTP Client للـ Main Site).
- [ ] **T-327** — `ReconcileTransactionsCommand` (Artisan command).
- [ ] **T-328** — GET Daily Summary من Main Site.
- [ ] **T-329** — مقارنة مع سجلاتنا اليومية.
- [ ] **T-330** — منطق اكتشاف Discrepancy.
- [ ] **T-331** — GET قائمة المعاملات المفقودة.
- [ ] **T-332** — إعادة معالجة المعاملات المفقودة برمجياً (IngestTransactionAction).
- [ ] **T-333** — Discrepancy Report email للأدمن.
- [ ] **T-334** — `EvaluateTiersCommand` للتخفيض اليومي.
- [ ] **T-335** — تحذير قبل 7 أيام من التخفيض (داخل نفس الـ command).
- [ ] **T-336** — `CleanupExpiredTokensCommand` (يحذف password tokens المنتهية).
- [ ] **T-337** — `ArchiveOldLogsCommand` (يؤرشف logs > 90 يوم أسبوعياً).
- [ ] **T-338** — تسجيل كل الـ commands في `app/Console/Kernel.php`.

---

## 🟡 Phase 5: ما قبل الإطلاق — أسبوع 13

- [ ] **T-339** — إعداد k6 (تثبيت + سكريبتات سيناريوهات).
- [ ] **T-340** — Load Test: 500 وكيل متزامن (Login + Dashboard).
- [ ] **T-341** — Load Test: Webhook 100 req/sec.
- [ ] **T-342** — تحديد وإصلاح الـ Bottlenecks.
- [ ] **T-343** — N+1 Query fixes: مراجعة كل Controllers مع Eager Loading.
- [ ] **T-344** — DB Indexes review (تأكد من INDEX على كل foreign keys + filterable columns).
- [ ] **T-345** — Cache opportunities (Settings, Tier Levels).
- [ ] **T-346** — OWASP ZAP automated scan.
- [ ] **T-347** — Manual SQL Injection test على كل forms.
- [ ] **T-348** — Manual XSS test (escape verification).
- [ ] **T-349** — CSRF Token verification على كل forms.
- [ ] **T-350** — Laravel Dusk: Test Login + 2FA flow.
- [ ] **T-351** — Laravel Dusk: Test Redemption flow كامل.
- [ ] **T-352** — Laravel Dusk: Test Admin approval flow.
- [ ] **T-353** — كتابة Agent User Guide (PDF).
- [ ] **T-354** — كتابة Admin User Guide (PDF).

---

## 🟢 Phase 6: الإطلاق — أسبوع 14

- [ ] **T-355** — إعداد Production Server (PHP-FPM + Nginx).
- [ ] **T-356** — SSL Certificate (Let's Encrypt أو شراء).
- [ ] **T-357** — Production `.env` (مفاتيح، DB، Mail credentials).
- [ ] **T-358** — Database Backup Strategy (يدوي - cron يومي).
- [ ] **T-359** — Soft Launch لـ 10-20 وكيل مختار.
- [ ] **T-360** — جمع Feedback (شخصياً + form).
- [ ] **T-361** — Hot-fixes بناءً على الـ Feedback.
- [ ] **T-362** — Full Launch announcement.
- [ ] **T-363** — Email campaign لكل الوكلاء.

---

## 📊 ملخص العد الكلي

| المرحلة | عدد المهام | المدة |
|---------|-----------|-------|
| Phase 0: Foundation | 40 | أسبوع |
| Phase 1: MVP Core | 98 (T-041 → T-138) | 3 أسابيع |
| Phase 2: Wallets + Redemption | 75 (T-139 → T-213) | 3 أسابيع |
| Phase 3: Admin Panel | 80 (T-214 → T-293) | 3 أسابيع |
| Phase 4: AM + Reports | 45 (T-294 → T-338) | أسبوعان |
| Phase 5: Pre-Launch | 16 (T-339 → T-354) | أسبوع |
| Phase 6: Launch | 9 (T-355 → T-363) | أسبوع |
| **الإجمالي** | **363** | **14 أسبوع** |

---

## 🎯 طريقة الاستخدام

### يومياً:
1. اختر المهمة التالية من القائمة (حسب Sprint الحالي).
2. ابدأ بالعمل عليها.
3. عند الإنجاز، ضع `[x]` وانقل المعلومة لـ `07_PROGRESS.md`.

### عند انتهاء Sprint:
- تأكد من تنفيذ كل مهام الـ Sprint.
- اختبر السيناريو الكامل End-to-End.
- علّم Milestone في `07_PROGRESS.md`.

### عند ظهور Blocker:
- سجّله فوراً في `07_PROGRESS.md`.
- قرّر: انتقل لمهمة أخرى أو حلّ الـ Blocker أولاً.

---

**🔗 روابط:**
- [خطة التطوير الكاملة](./06_DEVELOPMENT_PLAN.md)
- [ملف التتبع](./07_PROGRESS.md)
- [PRD](./03_PRD.md)
- [SRS](./04_SRS.md)
