<x-layouts.app title="Design System — 29FLY Loyalty">

<div class="min-h-screen bg-[var(--color-surface-secondary)]">
    {{-- Hero --}}
    <div class="bg-gradient-to-l from-[var(--color-primary-700)] to-[var(--color-primary-500)] text-white py-12 px-6">
        <div class="max-w-7xl mx-auto">
            <p class="text-sm opacity-90 mb-2">29FLY Loyalty</p>
            <h1 class="text-3xl md:text-4xl font-bold mb-2">الديزاين سيستيم</h1>
            <p class="opacity-90">مكتبة المكونات الموحّدة — مستوحاة من fly29.net</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-8 space-y-12">

        {{-- Colors --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">🎨 الألوان</h2>
            <p class="text-[var(--color-text-secondary)] mb-6">لوحة الألوان الرئيسية + تصنيفات الوكلاء.</p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach([
                    ['Primary', 'var(--color-primary-500)'],
                    ['CTA',     'var(--color-cta-500)'],
                    ['Accent',  'var(--color-accent-500)'],
                    ['Danger',  'var(--color-danger-500)'],
                    ['Diamond', 'var(--color-tier-diamond)'],
                    ['Gold',    'var(--color-tier-gold)'],
                    ['Silver',  'var(--color-tier-silver)'],
                    ['Bronze',  'var(--color-tier-bronze)'],
                ] as [$name, $color])
                    <div class="rounded-[var(--radius-md)] overflow-hidden border border-[var(--color-surface-border)] bg-white shadow-[var(--shadow-card)]">
                        <div class="h-20" style="background-color: {{ $color }};"></div>
                        <div class="p-3">
                            <p class="font-semibold text-sm">{{ $name }}</p>
                            <p class="text-xs text-[var(--color-text-muted)] font-latin">{{ $color }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Typography --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">✍️ Typography</h2>
            <p class="text-[var(--color-text-secondary)] mb-6">Cairo (عربي) + Inter (لاتيني).</p>

            <x-ui.card>
                <h1 class="text-4xl font-bold mb-2">عنوان رئيسي - H1</h1>
                <h2 class="text-2xl font-semibold mb-2">عنوان فرعي - H2</h2>
                <h3 class="text-xl font-semibold mb-2">عنوان ثالث - H3</h3>
                <p class="text-base mb-2">نص أساسي بحجم 16px — لوريم إيبسوم نص تجريبي للتأكد من جودة الخط.</p>
                <p class="text-sm text-[var(--color-text-secondary)] mb-2">نص صغير بحجم 14px للوصف الثانوي.</p>
                <p class="text-xs text-[var(--color-text-muted)]">نص دقيق 12px للملاحظات.</p>
                <p class="font-latin mt-3 text-sm">English Latin Sample: The quick brown fox jumps over the lazy dog. 123456789</p>
            </x-ui.card>
        </section>

        {{-- Buttons --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">🔘 الأزرار</h2>
            <p class="text-[var(--color-text-secondary)] mb-6">6 variants × 3 sizes.</p>

            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-[var(--color-text-muted)] mb-2">Variants</p>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button variant="primary">Primary</x-ui.button>
                            <x-ui.button variant="secondary">Secondary</x-ui.button>
                            <x-ui.button variant="cta">CTA - احجز الآن</x-ui.button>
                            <x-ui.button variant="danger">Danger</x-ui.button>
                            <x-ui.button variant="outline">Outline</x-ui.button>
                            <x-ui.button variant="ghost">Ghost</x-ui.button>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-[var(--color-text-muted)] mb-2">Sizes</p>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.button size="sm">صغير</x-ui.button>
                            <x-ui.button size="md">متوسط</x-ui.button>
                            <x-ui.button size="lg">كبير</x-ui.button>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-[var(--color-text-muted)] mb-2">States</p>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button :loading="true">جاري التحميل…</x-ui.button>
                            <x-ui.button :disabled="true">معطّل</x-ui.button>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </section>

        {{-- Form inputs --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">📝 حقول الإدخال</h2>

            <x-ui.card>
                <div class="grid md:grid-cols-2 gap-4">
                    <x-forms.form-group label="الاسم الكامل" for="name" required>
                        <x-ui.input id="name" placeholder="مثلاً: أحمد قدورة" />
                    </x-forms.form-group>

                    <x-forms.form-group label="البريد الإلكتروني" for="email" required hint="سنرسل لك تأكيداً">
                        <x-ui.input type="email" id="email" placeholder="agent@example.com" />
                    </x-forms.form-group>

                    <x-forms.form-group label="رقم الهاتف" for="phone" error="رقم الهاتف غير صحيح">
                        <x-ui.input id="phone" value="123" />
                    </x-forms.form-group>

                    <x-forms.form-group label="الدولة" for="country">
                        <x-ui.select id="country" :options="['SA' => 'السعودية', 'AE' => 'الإمارات', 'KW' => 'الكويت']" placeholder="اختر الدولة" />
                    </x-forms.form-group>

                    <x-forms.form-group label="ملاحظات" for="notes" class="md:col-span-2">
                        <x-ui.textarea id="notes" placeholder="اكتب ملاحظاتك هنا..." rows="3" />
                    </x-forms.form-group>
                </div>
            </x-ui.card>
        </section>

        {{-- Cards & Stats --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">🃏 البطاقات</h2>

            <div class="grid md:grid-cols-4 gap-4 mb-4">
                <x-ui.stat-card label="إجمالي الوكلاء" value="247" color="primary" trend="up" trendValue="+12%">
                    <x-slot:icon>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.stat-card>

                <x-ui.stat-card label="نقاط الشهر" value="3,420" color="cta" trend="up" trendValue="+25%">
                    <x-slot:icon>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.stat-card>

                <x-ui.stat-card label="استبدالات معلّقة" value="12" color="accent" trend="down" trendValue="-5%">
                    <x-slot:icon>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.stat-card>

                <x-ui.stat-card label="الإيرادات" value="$45,820" color="tier-gold" trend="up" trendValue="+18%">
                    <x-slot:icon>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.stat-card>
            </div>

            <x-ui.card title="بطاقة بـ Header" subtitle="هنا وصف فرعي للبطاقة" class="mb-4">
                <x-slot:actions>
                    <x-ui.button variant="ghost" size="sm">إجراء</x-ui.button>
                    <x-ui.button variant="primary" size="sm">حفظ</x-ui.button>
                </x-slot:actions>
                <p>محتوى البطاقة هنا — يمكن أن يحتوي على أي عنصر.</p>
            </x-ui.card>
        </section>

        {{-- Badges --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">🏷️ الـ Badges</h2>

            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-[var(--color-text-muted)] mb-2">Status Badges</p>
                        <div class="flex flex-wrap gap-2">
                            <x-ui.badge variant="neutral" :dot="true">معلّق</x-ui.badge>
                            <x-ui.badge variant="primary" :dot="true">قيد المراجعة</x-ui.badge>
                            <x-ui.badge variant="success" :dot="true">مقبول</x-ui.badge>
                            <x-ui.badge variant="warning" :dot="true">تحذير</x-ui.badge>
                            <x-ui.badge variant="danger" :dot="true">مرفوض</x-ui.badge>
                            <x-ui.badge variant="info">جديد</x-ui.badge>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-[var(--color-text-muted)] mb-2">Tier Badges</p>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.tier-badge tier="diamond" size="lg" />
                            <x-ui.tier-badge tier="gold" />
                            <x-ui.tier-badge tier="silver" />
                            <x-ui.tier-badge tier="bronze" size="sm" />
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </section>

        {{-- Alerts --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">📢 التنبيهات</h2>

            <div class="space-y-3">
                <x-ui.alert variant="success" title="نجح الحفظ">تم حفظ البيانات بنجاح في قاعدة البيانات.</x-ui.alert>
                <x-ui.alert variant="warning" title="تنبيه">باقي 7 أيام على إعادة تقييم التصنيف.</x-ui.alert>
                <x-ui.alert variant="danger" title="خطأ" :dismissible="true">فشل الاتصال بقاعدة البيانات. أعد المحاولة.</x-ui.alert>
                <x-ui.alert variant="info">يمكنك تحويل نقاطك إلى رصيد نقدي بدءاً من 800 نقطة.</x-ui.alert>
            </div>
        </section>

        {{-- Progress --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">📊 شريط التقدم</h2>

            <x-ui.card>
                <div class="space-y-4">
                    <x-ui.progress :value="15" :max="20" color="tier-gold" label="التقدم نحو Gold" />
                    <x-ui.progress :value="800" :max="1000" color="cta" label="نقاط للوصول لباكج مجاني" />
                    <x-ui.progress :value="45" :max="100" color="primary" label="نسبة الإنجاز" />
                    <x-ui.progress :value="3" :max="10" color="danger" label="محاولات تسجيل الدخول الفاشلة" size="sm" />
                </div>
            </x-ui.card>
        </section>

        {{-- Modal --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">🪟 النوافذ المنبثقة</h2>

            <x-ui.card>
                <div class="flex flex-wrap gap-2">
                    <x-ui.button x-on:click="$dispatch('open-modal', 'demo-modal')">افتح Modal</x-ui.button>
                    <x-ui.button variant="danger" x-on:click="$dispatch('open-modal', 'confirm-delete')">احذف (مع تأكيد)</x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.modal name="demo-modal" title="نافذة تجريبية" size="md">
                <p class="text-[var(--color-text-secondary)]">هذا محتوى تجريبي للنافذة المنبثقة. اضغط على X أو خارج النافذة للإغلاق.</p>

                <x-slot:footer>
                    <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', 'demo-modal')">إلغاء</x-ui.button>
                    <x-ui.button variant="primary" x-on:click="$dispatch('close-modal', 'demo-modal')">حفظ</x-ui.button>
                </x-slot:footer>
            </x-ui.modal>

            <x-ui.confirm-modal
                name="confirm-delete"
                title="تأكيد الحذف"
                message="هل أنت متأكد من حذف هذا العنصر؟ لا يمكن التراجع."
                confirmText="نعم، احذف"
                variant="danger"
            />
        </section>

        {{-- Tabs --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">📑 التبويبات</h2>

            <x-ui.card>
                <x-ui.tabs :tabs="['profile' => 'الملف الشخصي', 'wallet' => 'المحفظة', 'history' => 'السجل']" default="profile">
                    <x-ui.tab-panel key="profile">
                        <p class="text-[var(--color-text-secondary)]">محتوى تبويب <strong>الملف الشخصي</strong>.</p>
                    </x-ui.tab-panel>
                    <x-ui.tab-panel key="wallet">
                        <p class="text-[var(--color-text-secondary)]">محتوى تبويب <strong>المحفظة</strong>.</p>
                    </x-ui.tab-panel>
                    <x-ui.tab-panel key="history">
                        <p class="text-[var(--color-text-secondary)]">محتوى تبويب <strong>السجل</strong>.</p>
                    </x-ui.tab-panel>
                </x-ui.tabs>
            </x-ui.card>
        </section>

        {{-- Table --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">📋 الجداول</h2>

            <x-ui.table :headers="['الوكيل', 'التصنيف', 'الرصيد', 'الحالة', 'إجراءات']">
                @foreach([
                    ['name' => 'علاء التركي',  'tier' => 'gold',    'balance' => 2450, 'status' => 'success'],
                    ['name' => 'نور القاسم',   'tier' => 'silver',  'balance' => 980,  'status' => 'success'],
                    ['name' => 'سامر الحلبي',  'tier' => 'diamond', 'balance' => 5120, 'status' => 'warning'],
                    ['name' => 'فاطمة العمري', 'tier' => 'bronze',  'balance' => 320,  'status' => 'danger'],
                ] as $row)
                    <x-ui.table-row>
                        <x-ui.table-cell>{{ $row['name'] }}</x-ui.table-cell>
                        <x-ui.table-cell><x-ui.tier-badge :tier="$row['tier']" size="sm" /></x-ui.table-cell>
                        <x-ui.table-cell><span class="font-semibold">{{ number_format($row['balance']) }}</span> <span class="text-xs text-[var(--color-text-muted)]">نقطة</span></x-ui.table-cell>
                        <x-ui.table-cell>
                            <x-ui.badge :variant="$row['status']" :dot="true">
                                {{ ['success' => 'نشط', 'warning' => 'تحذير', 'danger' => 'معلّق'][$row['status']] }}
                            </x-ui.badge>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <x-ui.button variant="ghost" size="sm">عرض</x-ui.button>
                        </x-ui.table-cell>
                    </x-ui.table-row>
                @endforeach
            </x-ui.table>

            <x-ui.pagination :currentPage="2" :totalPages="10" />
        </section>

        {{-- Empty State --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">📭 الحالة الفارغة</h2>

            <x-ui.card padding="none">
                <x-ui.empty-state title="لا توجد معاملات بعد" description="ابدأ بأول عملية بيع لتظهر هنا.">
                    <x-slot:actions>
                        <x-ui.button variant="cta">ابدأ الآن</x-ui.button>
                    </x-slot:actions>
                </x-ui.empty-state>
            </x-ui.card>
        </section>

        {{-- Spinner + Tooltip --}}
        <section>
            <h2 class="text-2xl font-bold mb-1">⏳ أخرى</h2>

            <x-ui.card>
                <div class="flex flex-wrap items-center gap-6">
                    <div class="flex items-center gap-3">
                        <x-ui.spinner size="sm" />
                        <x-ui.spinner size="md" />
                        <x-ui.spinner size="lg" />
                    </div>

                    <x-ui.tooltip text="هذا تلميح مفيد!">
                        <x-ui.button variant="outline" size="sm">مرّر فوقي</x-ui.button>
                    </x-ui.tooltip>
                </div>
            </x-ui.card>
        </section>

        <footer class="text-center py-8 text-sm text-[var(--color-text-muted)]">
            29FLY Loyalty Design System v1.0 • {{ date('Y') }}
        </footer>
    </div>
</div>

</x-layouts.app>
