<x-layouts.admin
    :title="'API Log #' . $log->id"
    :pageTitle="'API Log #' . $log->id"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'سجل الـ API', 'href' => route('admin.api-logs')],
        ['label' => '#' . $log->id],
    ]"
>
    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Meta column --}}
        <div class="lg:col-span-1 space-y-4">
            <x-ui.card title="معلومات الطلب">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">الطريقة</dt>
                        <dd class="font-latin font-bold">{{ $log->method }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Endpoint</dt>
                        <dd class="font-latin text-end break-all">{{ $log->endpoint }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">الحالة</dt>
                        <dd class="font-latin">{{ $log->status }} ({{ $log->response_code }})</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Reference ID</dt>
                        <dd class="font-latin text-end break-all">{{ $log->reference_id ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">IP</dt>
                        <dd class="font-latin">{{ $log->ip_address ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">المدة</dt>
                        <dd class="font-latin">{{ $log->duration_ms ?? '—' }}ms</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">API Key</dt>
                        <dd class="font-latin text-xs">{{ $log->api_key_used ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">التاريخ</dt>
                        <dd>{{ $log->created_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                </dl>
            </x-ui.card>
        </div>

        {{-- Bodies column --}}
        <div class="lg:col-span-2 space-y-4">
            <x-ui.card title="Request Headers">
                <pre class="text-xs font-latin bg-slate-50 border border-slate-200 rounded-lg p-3 overflow-x-auto max-h-60 overflow-y-auto" dir="ltr">{{ json_encode($log->request_headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '—' }}</pre>
            </x-ui.card>

            <x-ui.card title="Request Body">
                <pre class="text-xs font-latin bg-slate-50 border border-slate-200 rounded-lg p-3 overflow-x-auto max-h-80 overflow-y-auto" dir="ltr">{{ json_encode($log->request_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '—' }}</pre>
            </x-ui.card>

            <x-ui.card title="Response Body">
                <pre class="text-xs font-latin bg-slate-50 border border-slate-200 rounded-lg p-3 overflow-x-auto max-h-80 overflow-y-auto" dir="ltr">{{ json_encode($log->response_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '—' }}</pre>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
