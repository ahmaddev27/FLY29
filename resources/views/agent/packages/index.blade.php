<x-layouts.agent
    title="الباكجات المجانية"
    pageTitle="الباكجات المجانية"
    :breadcrumbs="[
        ['label' => 'الرئيسية', 'href' => route('agent.dashboard')],
        ['label' => 'الباكجات المجانية'],
    ]"
>

    {{-- Balance banner --}}
    <div class="bg-white rounded-[var(--radius-md)] border border-[var(--color-surface-border)] p-4 mb-6 flex items-center justify-between gap-3 shadow-[var(--shadow-card)]">
        <div>
            <p class="text-xs text-[var(--color-text-secondary)]">رصيد محفظة الباكجات</p>
            <p class="text-2xl font-bold text-[var(--color-text-primary)]" dir="ltr">
                {{ number_format($balance) }}
                <span class="text-sm font-normal text-[var(--color-text-secondary)]">نقطة</span>
            </p>
        </div>
        <x-ui.button variant="ghost" size="sm" href="{{ route('agent.redemptions.index') }}">
            طلباتي السابقة ←
        </x-ui.button>
    </div>

    @if($packages->isEmpty())
        <x-ui.card>
            <x-ui.empty-state
                title="لا توجد باكجات متاحة حالياً"
                description="يتم تحديث قائمة الباكجات دورياً. تابع لوحتك للجديد."
            />
        </x-ui.card>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($packages as $pkg)
                <div class="bg-white rounded-[var(--radius-lg)] border border-[var(--color-surface-border)] shadow-[var(--shadow-card)] overflow-hidden flex flex-col">
                    {{-- Image --}}
                    @if($pkg->image_url)
                        <img src="{{ asset($pkg->image_url) }}" alt="{{ $pkg->name }}" class="w-full h-44 object-cover">
                    @else
                        <div class="w-full h-44 bg-gradient-to-br from-[var(--color-primary-100)] to-[var(--color-primary-50)] flex items-center justify-center">
                            <svg class="h-16 w-16 text-[var(--color-primary-500)] opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                    @endif

                    {{-- Body --}}
                    <div class="p-4 flex flex-col flex-1">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h3 class="font-bold text-[var(--color-text-primary)] flex-1">{{ $pkg->name }}</h3>
                            @if($pkg->duration_days)
                                <x-ui.badge variant="info" size="sm">
                                    <span dir="ltr">{{ $pkg->duration_days }}</span> يوم
                                </x-ui.badge>
                            @endif
                        </div>

                        <p class="text-xs text-[var(--color-text-secondary)] mb-2">📍 {{ $pkg->destination }}</p>

                        @if($pkg->description)
                            <p class="text-sm text-[var(--color-text-secondary)] mb-3 line-clamp-3">{{ $pkg->description }}</p>
                        @endif

                        {{-- Points --}}
                        <div class="mt-auto pt-3 border-t border-[var(--color-surface-divider)]">
                            <div class="flex items-baseline justify-between mb-3">
                                <span class="text-xs text-[var(--color-text-secondary)]">السعر</span>
                                <span class="text-xl font-bold text-[var(--color-cta-700)]" dir="ltr">
                                    {{ number_format($pkg->points_required) }}
                                    <span class="text-xs text-[var(--color-text-secondary)] font-normal">نقطة</span>
                                </span>
                            </div>

                            @if($pkg->affordable)
                                <x-ui.button
                                    variant="cta"
                                    :full="true"
                                    :auto-loading="false"
                                    x-on:click="$dispatch('open-modal', 'redeem-pkg-{{ $pkg->id }}')"
                                >
                                    استبدال الآن
                                </x-ui.button>
                            @else
                                <div class="text-center">
                                    <p class="text-xs text-[var(--color-warning-700)] mb-2">
                                        ينقصك <strong dir="ltr">{{ number_format($pkg->missing) }}</strong> نقطة
                                    </p>
                                    <x-ui.button variant="outline" :full="true" :disabled="true" :auto-loading="false">
                                        رصيد غير كافٍ
                                    </x-ui.button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Confirmation modal --}}
                    @if($pkg->affordable)
                        <x-ui.modal :name="'redeem-pkg-' . $pkg->id" title="تأكيد الاستبدال" size="sm">
                            <div class="text-center">
                                <p class="text-[var(--color-text-secondary)] mb-3">
                                    سيتم استبدال نقاطك بهذا الباكج فوراً. لا يمكن التراجع.
                                </p>
                                <div class="bg-[var(--color-surface-tertiary)] rounded-[var(--radius-md)] p-4 space-y-2 text-start">
                                    <div class="flex justify-between"><span class="text-sm text-[var(--color-text-secondary)]">الباكج:</span> <span class="font-bold">{{ $pkg->name }}</span></div>
                                    <div class="flex justify-between"><span class="text-sm text-[var(--color-text-secondary)]">الوجهة:</span> <span>{{ $pkg->destination }}</span></div>
                                    <div class="flex justify-between"><span class="text-sm text-[var(--color-text-secondary)]">السعر:</span> <span class="font-bold text-[var(--color-cta-700)]" dir="ltr">{{ number_format($pkg->points_required) }} نقطة</span></div>
                                    <div class="flex justify-between border-t border-[var(--color-surface-divider)] pt-2 mt-2">
                                        <span class="text-sm text-[var(--color-text-secondary)]">الرصيد بعد الاستبدال:</span>
                                        <span class="font-bold" dir="ltr">{{ number_format($balance - $pkg->points_required) }} نقطة</span>
                                    </div>
                                </div>
                            </div>

                            <x-slot:footer>
                                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', 'redeem-pkg-{{ $pkg->id }}')">إلغاء</x-ui.button>
                                <form method="POST" action="{{ route('agent.packages.redeem', $pkg) }}" class="inline">
                                    @csrf
                                    <x-ui.button type="submit" variant="cta" loadingText="جاري الاستبدال…">
                                        نعم، استبدل
                                    </x-ui.button>
                                </form>
                            </x-slot:footer>
                        </x-ui.modal>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</x-layouts.agent>
