@props([
    'headers' => [],   // ['الاسم', 'البريد', ...] OR ['name' => 'الاسم', ...]
    'striped' => false,
    'hover'   => true,
])

<div {{ $attributes->merge(['class' => 'overflow-x-auto bg-white rounded-[var(--radius-md)] border border-[var(--color-surface-border)] shadow-[var(--shadow-card)]']) }}>
    <table class="w-full text-right">
        @if(!empty($headers))
            <thead class="bg-[var(--color-surface-tertiary)] border-b border-[var(--color-surface-border)]">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-4 py-3 text-sm font-semibold text-[var(--color-text-secondary)] whitespace-nowrap">
                            {{ is_array($header) ? ($header['label'] ?? '') : $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody class="divide-y divide-[var(--color-surface-divider)]">
            {{ $slot }}
        </tbody>
    </table>
</div>
