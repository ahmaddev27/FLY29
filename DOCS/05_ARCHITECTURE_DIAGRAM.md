# المخطط المعماري التقني (Technical Architecture)
## برنامج ولاء وكلاء 29FLY

> **الإصدار:** 1.0
> **التاريخ:** نوفمبر 2026
> **التنسيق:** Mermaid Diagrams (تُرسم تلقائيًا على GitHub/GitLab)
> **التصنيف:** سري — للاستخدام الداخلي

---

## 1. نظرة شاملة على البنية (System Overview)

```mermaid
graph TB
    subgraph Users["👥 المستخدمون"]
        Agent["🧑‍💼 Agent<br/>وكيل سفر"]
        AM["👔 Account Manager<br/>مدير حساب"]
        Admin["👑 Admin<br/>مدير البرنامج"]
    end

    subgraph External["🌐 الأنظمة الخارجية"]
        MainSite["🏢 Main 29FLY Site<br/>الموقع الرئيسي"]
        SMTP["📧 SMTP Service"]
        SMS["📱 SMS Provider"]
        S3["☁️ Object Storage<br/>S3-Compatible"]
    end

    subgraph LB["⚖️ Load Balancer"]
        Nginx["Nginx Reverse Proxy<br/>SSL Termination + Rate Limiting"]
    end

    subgraph App["🚀 Application Tier"]
        Web1["PHP-FPM Worker 1<br/>Laravel 11"]
        Web2["PHP-FPM Worker 2<br/>Laravel 11"]
        WebN["PHP-FPM Worker N<br/>Auto-scaling"]
    end

    subgraph Workers["⚙️ Background Workers"]
        Horizon["Laravel Horizon<br/>Queue Manager"]
        QW1["Queue Worker 1<br/>notifications"]
        QW2["Queue Worker 2<br/>webhooks"]
        QW3["Queue Worker 3<br/>reports"]
        Cron["Scheduler<br/>(Cron Jobs)"]
    end

    subgraph Data["💾 Data Tier"]
        MySQL[("🗄️ MySQL 8.0<br/>Primary")]
        MySQLR[("🗄️ MySQL Replica<br/>Read-only<br/>(Phase 2)")]
        Redis[("⚡ Redis 7<br/>Cache + Queue + Session")]
    end

    subgraph Observability["📊 Observability"]
        Sentry["🐛 Sentry<br/>Error Tracking"]
        Prom["📈 Prometheus"]
        Graf["📊 Grafana"]
    end

    Agent -->|HTTPS| Nginx
    AM -->|HTTPS| Nginx
    Admin -->|HTTPS 2FA| Nginx
    MainSite -->|Webhook HMAC| Nginx

    Nginx --> Web1
    Nginx --> Web2
    Nginx --> WebN

    Web1 --> MySQL
    Web2 --> MySQL
    WebN --> MySQL
    Web1 -.read.-> MySQLR
    Web1 --> Redis
    Web2 --> Redis
    WebN --> Redis

    Web1 -->|dispatch jobs| Redis
    Redis -->|consume| Horizon
    Horizon --> QW1
    Horizon --> QW2
    Horizon --> QW3
    Cron --> MySQL
    Cron -->|trigger| Redis

    QW1 --> SMTP
    QW1 --> SMS
    QW2 --> MySQL
    QW3 --> S3

    Web1 -.errors.-> Sentry
    Horizon -.metrics.-> Prom
    Prom --> Graf

    style MainSite fill:#FFB84D
    style MySQL fill:#4479A1,color:#fff
    style Redis fill:#DC382D,color:#fff
    style Nginx fill:#009639,color:#fff
```

---

## 2. تدفق استقبال المعاملة (Transaction Ingestion Flow)

أهم flow في النظام بأكمله — يجب فهمه بدقة من قبل كل مطور.

```mermaid
sequenceDiagram
    autonumber
    participant MS as 🏢 Main Site
    participant LB as Nginx
    participant API as Laravel API
    participant Auth as AuthMiddleware
    participant Idem as IdempotencyCheck
    participant DB as MySQL
    participant Q as Redis Queue
    participant Tier as TierService
    participant Notif as NotifService
    participant Agent as 🧑 Agent

    MS->>LB: POST /api/v1/transactions/ingest<br/>+ X-API-Key + X-Signature
    LB->>API: Forward request
    API->>Auth: Validate API Key
    Auth->>Auth: HMAC-SHA256(body) == X-Signature?
    
    alt Invalid signature
        Auth-->>MS: 401 Unauthorized
        Auth->>DB: Log suspicious attempt
    end

    Auth->>Idem: Check reference_id
    Idem->>DB: SELECT * FROM transactions WHERE reference_id = ?
    
    alt Duplicate found
        Idem-->>MS: 200 OK<br/>{status: duplicate_ignored}
        Idem->>DB: Log in api_logs
    end

    Idem->>API: Proceed
    API->>DB: BEGIN TRANSACTION
    API->>DB: Read agent + settings (with lock)
    
    alt Agent suspended
        API->>DB: Save to pending_transactions
        API->>DB: COMMIT
        API-->>MS: 422 Agent suspended
    end

    API->>API: Calculate points<br/>(package_based / amount_based)
    API->>DB: INSERT transactions (+ config_snapshot)
    API->>DB: UPDATE cash_wallet_points<br/>UPDATE package_wallet_points
    API->>DB: INSERT points_history (x2 wallets)
    API->>DB: COMMIT
    
    API->>Q: dispatch(EvaluateTierJob)
    API->>Q: dispatch(NotifyPointsEarnedJob)
    
    API-->>MS: 200 OK<br/>{points_awarded, new_balance}
    
    Q->>Tier: Process tier evaluation
    Tier->>DB: Check threshold
    
    alt Upgrade qualified
        Tier->>DB: UPDATE agents.current_tier
        Tier->>DB: INSERT tier_history
        Tier->>Q: dispatch(NotifyTierUpgradeJob)
    end
    
    Q->>Notif: Send notification
    Notif->>SMTP: Email (async)
    Notif->>DB: INSERT in-app notification
    Agent->>Agent: Sees notification (next page load / WebSocket)
```

---

## 3. تدفق Cron Job — إعادة تقييم التصنيف

```mermaid
flowchart TD
    Start([⏰ Cron 02:00 Daily]) --> Query[Query: agents WHERE<br/>tier_valid_until <= NOW<br/>AND current_tier != 'bronze']
    
    Query --> Chunk{Records<br/>found?}
    Chunk -->|No| Skip[Skip + Log]
    Chunk -->|Yes| Loop[Chunk by 100<br/>process in parallel]
    
    Loop --> Calc[Calculate packages<br/>in evaluation window]
    
    Calc --> Mode{Mode?}
    Mode -->|calendar_month| Mode1[Count from<br/>1st of current month]
    Mode -->|rolling_30_days| Mode2[Count last 30 days]
    
    Mode1 --> Decide{Earned tier<br/>vs Current?}
    Mode2 --> Decide
    
    Decide -->|Same| Renew[Extend tier_valid_until<br/>+ 30 days]
    Decide -->|Lower| Downgrade[Apply downgrade<br/>+ Log in tier_history<br/>+ Dispatch notification]
    Decide -->|Higher| Note[Already upgraded sync<br/>in transaction flow<br/>—  no action needed]
    
    Renew --> Next
    Downgrade --> Next
    Note --> Next[Next agent]
    
    Next --> More{More<br/>chunks?}
    More -->|Yes| Loop
    More -->|No| Warn[Check warning candidates:<br/>tier_valid_until BETWEEN<br/>NOW AND NOW + 7d]
    
    Warn --> WarnLoop[For each at-risk agent:<br/>dispatch warning notification]
    
    WarnLoop --> Report[Generate daily summary<br/>email to Admin]
    Report --> End([✅ Done])
    
    Skip --> End
    
    style Start fill:#FF9F40
    style End fill:#4CAF50,color:#fff
    style Downgrade fill:#F44336,color:#fff
    style Renew fill:#2196F3,color:#fff
```

---

## 4. تدفق طلب التحويل النقدي

```mermaid
stateDiagram-v2
    [*] --> Initiated: Agent clicks "Request Cash"
    
    Initiated --> Validating: Submit form
    
    Validating --> Failed: < 800 points OR insufficient balance
    Failed --> [*]
    
    Validating --> Pending: Validation passed<br/>+ Lock points<br/>+ Notify Admin
    
    Pending --> Approved: Admin approves<br/>+ Final deduct<br/>+ Notify Agent
    Pending --> Rejected: Admin rejects (with reason)<br/>+ Unlock points<br/>+ Notify Agent
    Pending --> Cancelled: Agent cancels<br/>+ Unlock points
    
    Approved --> Fulfilled: Manual: Admin transfers funds<br/>via banking channel
    
    Rejected --> [*]
    Cancelled --> [*]
    Fulfilled --> [*]
    
    note right of Pending
        Points are LOCKED:
        available -= 800
        locked += 800
    end note
    
    note right of Approved
        Points are PERMANENTLY DEDUCTED:
        locked -= 800
        (NOT returned to available)
    end note
    
    note right of Rejected
        Points UNLOCKED:
        available += 800
        locked -= 800
    end note
```

---

## 5. نموذج البيانات (Entity Relationship Diagram)

```mermaid
erDiagram
    USERS ||--o| AGENTS : "has profile"
    USERS ||--o{ AUDIT_LOGS : "performs"
    USERS ||--o{ MESSAGES : "sends/receives"
    USERS ||--o{ NOTIFICATIONS : "receives"
    USERS ||--o{ USER_NOTIFICATION_PREFERENCES : "configures"
    
    AGENTS ||--|| CASH_WALLET_POINTS : "owns"
    AGENTS ||--|| PACKAGE_WALLET_POINTS : "owns"
    AGENTS ||--o{ TRANSACTIONS : "earns from"
    AGENTS ||--o{ REDEMPTION_REQUESTS : "creates"
    AGENTS ||--o{ POINTS_HISTORY : "tracked in"
    AGENTS ||--o{ TIER_HISTORY : "history of"
    AGENTS }o--|| AGENT_LEVELS : "current tier"
    AGENTS }o--o| USERS : "assigned to AM"
    
    REDEMPTION_REQUESTS }o--o| FREE_PACKAGES : "may target"
    REDEMPTION_REQUESTS }o--o| USERS : "processed by"
    
    POINTS_HISTORY }o--o| TRANSACTIONS : "from"
    POINTS_HISTORY }o--o| REDEMPTION_REQUESTS : "from"
    
    SYSTEM_SETTINGS }o--o| USERS : "updated by"
    
    USERS {
        bigint id PK
        enum role "agent/account_manager/admin/super_admin"
        string email UK
        string password_hash
        string full_name
        enum status "active/suspended/deleted"
        string two_factor_secret
        timestamp last_login_at
        timestamps created_updated_deleted
    }
    
    AGENTS {
        bigint id PK
        bigint user_id FK
        string external_agent_id UK "from Main Site"
        string business_name
        string license_number UK
        string country
        enum current_tier
        timestamp tier_valid_until
        bigint account_manager_id FK
        decimal pending_amount "for amount_based fractions"
    }
    
    AGENT_LEVELS {
        bigint id PK
        enum tier_name UK
        int min_packages_monthly
        int points_per_package
        decimal amount_per_point
        json benefits
    }
    
    TRANSACTIONS {
        bigint id PK
        bigint agent_id FK
        string reference_id UK "idempotency key"
        enum transaction_type "package/service"
        decimal amount_usd
        string destination
        int points_awarded
        json config_snapshot "ENHANCEMENT"
        timestamp transaction_date
    }
    
    CASH_WALLET_POINTS {
        bigint id PK
        bigint agent_id FK,UK
        int available_points
        int locked_points "held for pending requests"
        int lifetime_earned
        int lifetime_redeemed
    }
    
    PACKAGE_WALLET_POINTS {
        bigint id PK
        bigint agent_id FK,UK
        int available_points
        int locked_points
        int lifetime_earned
        int lifetime_redeemed
    }
    
    POINTS_HISTORY {
        bigint id PK
        bigint agent_id FK
        enum wallet_type "cash/package"
        bigint transaction_id FK
        bigint redemption_id FK
        int points_delta
        int balance_after
        enum source
        json config_snapshot
    }
    
    REDEMPTION_REQUESTS {
        bigint id PK
        bigint agent_id FK
        enum type "cash/package"
        int points
        decimal cash_value_usd
        bigint package_id FK
        enum status "pending/approved/rejected/cancelled/fulfilled"
        text rejection_reason
        bigint processed_by FK
    }
    
    TIER_HISTORY {
        bigint id PK
        bigint agent_id FK
        enum from_tier
        enum to_tier
        enum action "upgrade/downgrade/manual/initial"
        int packages_at_time
        timestamp valid_until
    }
    
    FREE_PACKAGES {
        bigint id PK
        string name
        string destination
        int points_required
        int duration_days
        text description
        string image_url
        boolean is_active
    }
    
    SYSTEM_SETTINGS {
        string key PK
        text value
        enum value_type
        text description
        bigint updated_by FK
    }
    
    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK
        string action
        string entity_type
        string entity_id
        json old_values
        json new_values
        string ip_address
    }
    
    NOTIFICATIONS {
        bigint id PK
        bigint user_id FK
        string type
        json data
        boolean is_read
        timestamp read_at
    }
    
    MESSAGES {
        bigint id PK
        bigint sender_id FK
        bigint receiver_id FK
        text body
        boolean is_read
    }
    
    USER_NOTIFICATION_PREFERENCES {
        bigint id PK
        bigint user_id FK
        string notification_type
        boolean email_enabled
        boolean sms_enabled
        boolean in_app_enabled
    }
```

---

## 6. البنية المعمارية للطبقات (Layered Architecture)

```mermaid
flowchart TB
    subgraph Presentation["🎨 Presentation Layer"]
        Blade["Blade Views<br/>+ Alpine.js<br/>+ Tailwind CSS"]
        APIControllers["API Controllers<br/>(Resource format)"]
    end
    
    subgraph Application["⚙️ Application Layer"]
        WebControllers["Web Controllers"]
        Middleware["Middleware<br/>Auth, RateLimit, AuditLog"]
        FormRequests["Form Requests<br/>(Validation)"]
        Jobs["Queueable Jobs"]
        Events["Events & Listeners"]
    end
    
    subgraph Domain["💼 Domain / Business Logic"]
        Services["Services<br/>(PointsService, TierService,<br/>RedemptionService, NotificationService)"]
        Actions["Single-Action Classes<br/>(IngestTransactionAction,<br/>ApproveRedemptionAction)"]
        Policies["Policies<br/>(Authorization)"]
        DTOs["DTOs<br/>(Data Transfer Objects)"]
    end
    
    subgraph Infrastructure["🔧 Infrastructure Layer"]
        Eloquent["Eloquent Models<br/>+ Repositories"]
        QueueDriver["Queue Driver<br/>(Redis)"]
        CacheDriver["Cache Driver<br/>(Redis)"]
        Mailer["Mailer (SMTP)"]
        Storage["Storage<br/>(S3 adapter)"]
        ExternalAPI["External API<br/>Clients (HTTP)"]
    end
    
    subgraph Data["💾 Data Stores"]
        DB[("MySQL")]
        Cache[("Redis")]
        Files[("S3 Storage")]
    end
    
    Blade --> WebControllers
    APIControllers --> Middleware
    WebControllers --> Middleware
    Middleware --> FormRequests
    FormRequests --> Services
    FormRequests --> Actions
    
    Services --> Eloquent
    Services --> Actions
    Actions --> Eloquent
    Services --> Jobs
    Services --> Events
    Events --> Jobs
    
    Eloquent --> DB
    QueueDriver --> Cache
    CacheDriver --> Cache
    Storage --> Files
    Jobs --> QueueDriver
    Services --> CacheDriver
    Services --> Mailer
    
    style Domain fill:#E3F2FD
    style Application fill:#FFF3E0
    style Presentation fill:#F3E5F5
    style Infrastructure fill:#E8F5E9
```

---

## 7. البنية الأمنية (Security Architecture)

```mermaid
flowchart LR
    subgraph External["🌍 Internet"]
        Client["Client / Webhook Source"]
    end
    
    subgraph Edge["🛡️ Edge Layer"]
        WAF["WAF / CDN<br/>(Cloudflare)"]
        DDOS["DDoS Protection"]
    end
    
    subgraph TLS["🔐 TLS Termination"]
        Nginx["Nginx<br/>TLS 1.3 only<br/>HSTS enabled"]
    end
    
    subgraph AppSec["🔒 Application Security"]
        RateLimit["Rate Limiter<br/>100 req/min/IP"]
        APIAuth["API Key Validation"]
        HMAC["HMAC Signature Check<br/>(SHA-256)"]
        Session["Session Auth<br/>(Redis-backed)"]
        TwoFA["2FA for Admins<br/>(TOTP)"]
        CSRF["CSRF Tokens"]
        XSSGuard["Output Escaping<br/>(Blade auto)"]
    end
    
    subgraph DataSec["🗄️ Data Security"]
        Encrypted["Encryption at rest<br/>(MySQL TDE)"]
        Bcrypt["Passwords:<br/>bcrypt cost 12"]
        AuditLog["Audit Log<br/>(immutable)"]
        Backup["Encrypted Backups<br/>(S3 SSE-KMS)"]
    end
    
    Client --> WAF
    WAF --> DDOS
    DDOS --> Nginx
    Nginx --> RateLimit
    RateLimit --> APIAuth
    APIAuth --> HMAC
    HMAC --> Session
    Session --> TwoFA
    TwoFA --> CSRF
    CSRF --> XSSGuard
    XSSGuard --> Encrypted
    Encrypted --> Bcrypt
    Bcrypt --> AuditLog
    AuditLog --> Backup
    
    style WAF fill:#FFB300
    style HMAC fill:#E53935,color:#fff
    style AuditLog fill:#1976D2,color:#fff
    style Bcrypt fill:#388E3C,color:#fff
```

---

## 8. بنية النشر (Deployment Architecture)

```mermaid
flowchart TB
    subgraph Internet["🌐 Internet"]
        Users["End Users<br/>(Agents, Admins)"]
        MainSite["Main 29FLY Site"]
    end
    
    subgraph CDN["☁️ CDN Layer"]
        CF["Cloudflare<br/>(CDN + WAF + DDoS)"]
    end
    
    subgraph DC["🏢 Production Data Center"]
        subgraph LBTier["⚖️ Load Balancer Tier"]
            LB1["Nginx LB Primary"]
            LB2["Nginx LB Standby"]
        end
        
        subgraph AppTier["🚀 Application Tier (Auto-scaling)"]
            App1["App Server 1<br/>PHP-FPM<br/>Laravel 11"]
            App2["App Server 2<br/>PHP-FPM<br/>Laravel 11"]
            AppN["App Server N<br/>(scales 2-10)"]
        end
        
        subgraph WorkerTier["⚙️ Worker Tier"]
            HorizonM["Horizon Master"]
            Worker1["Worker Pool 1<br/>(notifications)"]
            Worker2["Worker Pool 2<br/>(webhooks)"]
            Worker3["Worker Pool 3<br/>(reports)"]
            Sched["Scheduler<br/>(Cron)"]
        end
        
        subgraph DBTier["💾 Database Tier"]
            DBP[("MySQL Primary<br/>(write)")]
            DBR[("MySQL Replica<br/>(read)<br/>Phase 2")]
            RedisM[("Redis Master")]
            RedisR[("Redis Replica<br/>(failover)")]
        end
        
        subgraph StorageTier["📦 Storage"]
            S3["S3-Compatible<br/>Storage<br/>(reports, images)"]
        end
    end
    
    subgraph Monitoring["📊 Monitoring & Logging"]
        Sentry["Sentry<br/>(errors)"]
        Logs["Loki / ELK<br/>(logs)"]
        Metrics["Prometheus +<br/>Grafana"]
        Uptime["Uptime Robot<br/>(synthetic)"]
    end
    
    subgraph Backup["💼 Backup"]
        S3Backup["S3 Backup<br/>(encrypted)"]
    end
    
    Users -->|HTTPS| CF
    MainSite -->|HTTPS Webhook| CF
    CF --> LB1
    LB1 -.failover.-> LB2
    
    LB1 --> App1
    LB1 --> App2
    LB1 --> AppN
    
    App1 --> DBP
    App2 --> DBP
    AppN --> DBP
    App1 -.read.-> DBR
    
    App1 --> RedisM
    App2 --> RedisM
    AppN --> RedisM
    RedisM -.replicate.-> RedisR
    
    App1 --> S3
    
    RedisM --> HorizonM
    HorizonM --> Worker1
    HorizonM --> Worker2
    HorizonM --> Worker3
    Sched --> DBP
    Worker1 --> DBP
    
    App1 -.events.-> Sentry
    App1 -.logs.-> Logs
    HorizonM -.metrics.-> Metrics
    Uptime -.probe.-> LB1
    
    DBP -.daily.-> S3Backup
    
    style CF fill:#F38020,color:#fff
    style DBP fill:#4479A1,color:#fff
    style RedisM fill:#DC382D,color:#fff
    style HorizonM fill:#FF2D20,color:#fff
```

---

## 9. ترتيب التدفق الزمني للترقية (Tier Upgrade Sequence)

```mermaid
sequenceDiagram
    autonumber
    participant Main as Main Site
    participant API as Loyalty API
    participant DB as MySQL
    participant Q as Redis Queue
    participant TierSvc as TierService
    participant NotifSvc as NotificationService
    participant Email as SMTP
    participant Agent as Agent (UI)
    
    Note over Main,Agent: Scenario: Silver agent sells the 20th package (Gold threshold)
    
    Main->>API: POST /api/v1/transactions/ingest<br/>{package, 1500$, ref-998}
    API->>DB: Begin transaction
    API->>DB: INSERT transactions (points: 3, tier: silver)
    API->>DB: UPDATE wallets (+3 to each)
    API->>DB: INSERT points_history (x2)
    API->>DB: Commit
    
    API->>Q: dispatch(EvaluateTierJob)
    API-->>Main: 200 OK
    
    Q->>TierSvc: Process job
    TierSvc->>DB: COUNT packages this window<br/>WHERE agent_id = ? AND type='package'
    DB-->>TierSvc: count = 20
    
    TierSvc->>DB: SELECT thresholds FROM agent_levels
    DB-->>TierSvc: gold = 20, diamond = 30
    
    TierSvc->>TierSvc: Earned tier = gold<br/>(20 >= 20, < 30)
    TierSvc->>TierSvc: Current = silver, Earned = gold<br/>→ UPGRADE
    
    TierSvc->>DB: UPDATE agents SET current_tier='gold',<br/>tier_valid_until=NOW()+30d
    TierSvc->>DB: INSERT tier_history (upgrade, silver→gold)
    
    TierSvc->>Q: dispatch(NotifyTierUpgradeJob)
    
    Q->>NotifSvc: Process notification
    NotifSvc->>DB: Read user_notification_preferences
    NotifSvc->>DB: INSERT notifications (in-app)
    NotifSvc->>Email: Send "Congratulations! Promoted to Gold"
    
    Email-->>Agent: 📧 Email arrives
    
    Note over Agent: Agent opens dashboard
    Agent->>API: GET /api/agent/dashboard
    API-->>Agent: New tier: Gold 🥇<br/>+ unread notification badge
```

---

## 10. خطة قابلية التوسع المرحلية

```mermaid
gantt
    title خارطة التوسع التقني عبر مراحل النمو
    dateFormat YYYY-MM
    axisFormat %b %Y
    
    section Phase 1 (0-10K agents)
    Single Web + DB + Redis             :p1, 2026-11, 6M
    Daily backups                       :p1b, 2026-11, 6M
    
    section Phase 2 (10K-50K agents)
    Add Read Replica                    :p2a, 2027-05, 2M
    Horizontal Web scaling              :p2b, 2027-05, 2M
    Redis Sentinel for HA               :p2c, 2027-05, 2M
    
    section Phase 3 (50K-200K agents)
    Database sharding by agent_id       :p3a, 2027-09, 3M
    CDN for static assets               :p3b, 2027-09, 1M
    Dedicated workers per queue         :p3c, 2027-10, 2M
    
    section Phase 4 (200K+ agents)
    Move to microservices               :p4a, 2028-01, 6M
    Event sourcing for points           :p4b, 2028-03, 4M
    Multi-region deployment             :p4c, 2028-06, 3M
```

---

## 11. مصفوفة المسؤوليات (Service Responsibilities Matrix)

| Service / Class | المسؤولية الأساسية | Dependencies |
|----------------|--------------------|--------------|
| **AuthService** | تسجيل دخول، 2FA، استرجاع كلمات المرور | User Model, Mailer |
| **IngestTransactionAction** | معالجة Webhook بالكامل | PointsService, TierService, AuditService |
| **PointsService** | حساب النقاط، تحديث المحافظ، history | Settings, Wallet Models |
| **WalletService** | عمليات المحفظتين (lock, deduct, refund) | DB Transactions |
| **TierService** | تقييم وترقية وتخفيض التصنيفات | Agent Model, Settings |
| **RedemptionService** | منطق الاستبدال (cash, package) | WalletService, NotificationService |
| **NotificationService** | dispatch إشعارات متعددة القنوات | Queue, Mailer, SMS Driver |
| **SettingsService** | قراءة وحفظ الإعدادات مع Cache | Redis, AuditService |
| **AuditService** | تسجيل العمليات الحساسة | DB |
| **ReportService** | توليد التقارير وتصديرها | Excel, PDF generators |
| **ReconciliationService** | المطابقة مع Main Site (تحسين مقترح) | HTTP Client, DB |

---

## 12. ملاحظات معمارية ختامية

### مبادئ التصميم المُتبعة
1. **Single Responsibility:** كل Service له مسؤولية واحدة واضحة.
2. **Dependency Injection:** كل Service يُحقن عبر constructor، يسهّل الاختبار.
3. **Repository Pattern (اختياري):** لـ DB queries المعقدة، مع الإبقاء على Eloquent للبسيط.
4. **Event-Driven Updates:** الترقيات والإشعارات تتم عبر Events + Listeners async.
5. **Idempotency First:** كل endpoint قابل للاستدعاء عدة مرات بأمان.
6. **Config Externalization:** صفر `hardcoded` قيم في الكود — كل شيء في `system_settings`.

### الإطار الزمني للبناء
- **MVP (4 أسابيع):** Web + API + Auth + Wallets + Webhook + Basic Admin.
- **Phase 2 (أسبوعان):** Notifications + Redemption flows + Reports.
- **Phase 3 (أسبوعان):** Advanced Admin + Account Managers + Audit + Polish.
- **Phase 4 (أسبوع):** Load testing + Security audit + Production deployment.

### قرارات تقنية رئيسية وتوصيات
| القرار | التوصية | السبب |
|--------|----------|--------|
| Framework | Laravel 11 (ليس CodeIgniter) | Ecosystem، Queue، Eloquent، اختبارات |
| Frontend | Blade + Alpine.js (ليس React/Vue) | SSR، أبسط للنشر، يكفي للمتطلبات |
| Auth | Session-based (ليس JWT للـ Web) | أكثر أمانًا للـ Web، JWT للـ Mobile لاحقًا |
| Queue | Redis + Horizon (ليس DB queue) | الأداء والمرئية |
| Webhook Idempotency | reference_id UNIQUE constraint | بساطة، DB يضمنها |
| Tier Upgrade | Synchronous (مع نفس الـ request) | Agent يرى الترقية فورًا |
| Tier Downgrade | Async via Cron | لا يوجد سبب لفعلها sync |
| Reports > 1000 rows | Async + Email link | تجنب timeouts |

---

**نهاية المخطط المعماري**
