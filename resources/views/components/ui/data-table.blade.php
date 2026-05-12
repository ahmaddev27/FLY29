@props([
    'paginator'        => null,         // LengthAwarePaginator from controller (optional)
    'search'           => true,         // bool — show search input
    'searchPlaceholder' => 'بحث...',
    'searchParam'      => 'q',          // query param name for search
    'filters'          => [],           // [['name' => 'status', 'label' => 'الحالة', 'options' => ['key' => 'label', ...]], ...]
    'perPageOptions'   => [10, 25, 50, 100],
    'showPerPage'      => true,
    'title'            => null,         // optional header title
    'subtitle'         => null,         // optional header subtitle
    'isEmpty'          => false,        // pass true to render the empty-state slot instead of the table
])

@php
    $currentQuery = request()->query();
    $currentSearch = $currentQuery[$searchParam] ?? '';
    $currentPerPage = (int) ($currentQuery['per_page'] ?? 25);

    // Build form action preserving non-filter params
    $preserveQuery = collect($currentQuery)
        ->except(array_merge([$searchParam, 'per_page', 'page'], array_column($filters, 'name')))
        ->toArray();
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden']) }}>

    {{-- Header --}}
    @if($title || $subtitle || isset($toolbar))
        <div class="flex flex-wrap items-start justify-between gap-3 px-5 py-4 border-b border-slate-200">
            <div>
                @if($title)
                    <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($toolbar)
                <div class="flex flex-wrap items-center gap-2">{{ $toolbar }}</div>
            @endisset
        </div>
    @endif

    {{-- Filters bar --}}
    @if($search || !empty($filters) || $showPerPage)
        <form method="GET" class="px-5 py-3 bg-slate-50/60 border-b border-slate-200">
            {{-- Preserve unrelated query params --}}
            @foreach($preserveQuery as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ is_array($value) ? json_encode($value) : $value }}">
            @endforeach

            <div class="flex flex-wrap items-end gap-3">

                {{-- Search --}}
                @if($search)
                    <div class="flex-1 min-w-[200px]">
                        <label for="dt-search-{{ $searchParam }}" class="block text-xs font-medium text-slate-600 mb-1">بحث</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-slate-400">
                                <x-ui.icon name="search" size="sm" />
                            </div>
                            <input
                                type="search"
                                id="dt-search-{{ $searchParam }}"
                                name="{{ $searchParam }}"
                                value="{{ $currentSearch }}"
                                placeholder="{{ $searchPlaceholder }}"
                                class="block w-full bg-white border border-slate-300 rounded-lg ps-10 pe-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-100)] focus:border-[var(--color-primary-500)]"
                            >
                        </div>
                    </div>
                @endif

                {{-- Filters --}}
                @foreach($filters as $filter)
                    @php
                        $currentValue = $currentQuery[$filter['name']] ?? '';
                        $filterType   = $filter['type'] ?? 'select';
                        $fieldClasses = 'block w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-100)] focus:border-[var(--color-primary-500)]';
                    @endphp
                    <div class="min-w-[150px]">
                        <label for="dt-filter-{{ $filter['name'] }}" class="block text-xs font-medium text-slate-600 mb-1">
                            {{ $filter['label'] ?? $filter['name'] }}
                        </label>

                        @if($filterType === 'date')
                            <input type="date"
                                id="dt-filter-{{ $filter['name'] }}"
                                name="{{ $filter['name'] }}"
                                value="{{ $currentValue }}"
                                class="{{ $fieldClasses }}"
                            >
                        @else
                            <select
                                id="dt-filter-{{ $filter['name'] }}"
                                name="{{ $filter['name'] }}"
                                class="{{ $fieldClasses }}"
                            >
                                <option value="">{{ $filter['placeholder'] ?? 'الكل' }}</option>
                                @foreach($filter['options'] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected((string) $currentValue === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                @endforeach

                {{-- Per page --}}
                @if($showPerPage)
                    <div class="min-w-[100px]">
                        <label for="dt-per-page" class="block text-xs font-medium text-slate-600 mb-1">عرض</label>
                        <select
                            id="dt-per-page"
                            name="per_page"
                            onchange="this.form.submit()"
                            class="block w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-100)] focus:border-[var(--color-primary-500)]"
                        >
                            @foreach($perPageOptions as $opt)
                                <option value="{{ $opt }}" @selected($currentPerPage === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex gap-2">
                    <x-ui.button type="submit" variant="primary" size="md">تطبيق</x-ui.button>
                    @if($currentSearch || collect($filters)->pluck('name')->some(fn ($n) => ! empty(request($n))))
                        <x-ui.button variant="ghost" size="md" :href="url()->current() . (count($preserveQuery) ? '?' . http_build_query($preserveQuery) : '')" :auto-loading="false">
                            مسح
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </form>
    @endif

    {{-- Table content --}}
    <div class="relative">
        @if($isEmpty)
            {{ $empty ?? '' }}
        @else
            <div class="overflow-x-auto">
                {{ $slot }}
            </div>
        @endif
    </div>

    {{-- Footer: result count + pagination --}}
    @if($paginator && method_exists($paginator, 'links'))
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 bg-slate-50/40 border-t border-slate-200">
            <p class="text-xs text-slate-600">
                عرض <strong>{{ $paginator->firstItem() ?? 0 }}</strong>
                إلى <strong>{{ $paginator->lastItem() ?? 0 }}</strong>
                من <strong>{{ $paginator->total() }}</strong> نتيجة
            </p>
            <div class="text-xs">
                {{ $paginator->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>
