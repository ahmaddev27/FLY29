@props(['from', 'to'])

@php
    $fromStr = \Illuminate\Support\Carbon::parse($from)->toDateString();
    $toStr   = \Illuminate\Support\Carbon::parse($to)->toDateString();
@endphp

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <div class="grid sm:grid-cols-3 gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">من تاريخ</label>
            <x-ui.input type="date" name="from" :value="request('from', $fromStr)" />
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">إلى تاريخ</label>
            <x-ui.input type="date" name="to" :value="request('to', $toStr)" />
        </div>
        <div class="flex gap-2 justify-end">
            @if(request()->hasAny(['from', 'to']))
                <x-ui.button variant="ghost" size="sm" href="{{ url()->current() }}">إعادة ضبط</x-ui.button>
            @endif
            <x-ui.button type="submit" variant="primary" size="sm">
                <x-ui.icon name="search" size="sm" /> تطبيق
            </x-ui.button>
            {{ $slot ?? '' }}
        </div>
    </div>
</form>
