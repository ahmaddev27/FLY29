@props(['key'])

<div x-show="activeTab === @js($key)" role="tabpanel">
    {{ $slot }}
</div>
