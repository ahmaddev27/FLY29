<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سجل النقاط — {{ $agent->business_name }}</title>
    <style>
        body { font-family: xbriyaz, sans-serif; font-size: 11px; color: #222; }
        h1   { margin: 0 0 4px; font-size: 18px; color: #0066CC; font-weight: bold; }
        .meta { font-size: 10px; color: #666; margin-bottom: 16px; }
        .meta strong { color: #222; }
        .meta div { margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: right; }
        th { background: #f5f5f5; font-weight: bold; font-size: 11px; }
        .badge-pkg { background: #EFF6FF; color: #1D4ED8; padding: 1px 6px; border-radius: 3px; font-size: 10px; }
        .badge-svc { background: #F5F5F5; color: #666;    padding: 1px 6px; border-radius: 3px; font-size: 10px; }
        .points { color: #047857; font-weight: bold; }
        .footer { margin-top: 12px; font-size: 9px; color: #999; text-align: center; }
        .empty  { padding: 30px; text-align: center; color: #666; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <h1>سجل النقاط</h1>
    <div class="meta">
        <div><strong>الوكيل:</strong> {{ $agent->business_name }} — {{ $agent->external_agent_id }}</div>
        <div><strong>تاريخ التقرير:</strong> {{ $generatedAt->format('Y-m-d H:i') }}</div>
        @if(!empty($filters['from']) || !empty($filters['to']))
            <div><strong>الفترة:</strong>
                {{ $filters['from'] ?? '...' }} → {{ $filters['to'] ?? '...' }}
            </div>
        @endif
        @if(!empty($filters['type']))
            <div><strong>النوع:</strong> {{ $filters['type'] === 'package' ? 'باكج' : 'خدمة' }}</div>
        @endif
    </div>

    @if($transactions->isEmpty())
        <div class="empty">لا توجد معاملات تطابق الفلاتر.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>النوع</th>
                    <th>الوجهة</th>
                    <th>المبلغ (USD)</th>
                    <th>النقاط</th>
                    <th>المرجع</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $txn)
                    <tr>
                        <td class="nowrap">{{ $txn->transaction_date->format('Y-m-d H:i') }}</td>
                        <td>
                            <span class="{{ $txn->transaction_type === 'package' ? 'badge-pkg' : 'badge-svc' }}">
                                {{ $txn->transaction_type === 'package' ? 'باكج' : 'خدمة' }}
                            </span>
                        </td>
                        <td>{{ $txn->destination ?? '—' }}</td>
                        <td class="nowrap">${{ number_format($txn->amount_usd, 2) }}</td>
                        <td class="points nowrap">+{{ $txn->points_awarded }}</td>
                        <td class="nowrap">{{ $txn->reference_id }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        29FLY Loyalty Program — تم توليد هذا التقرير تلقائياً
    </div>
</body>
</html>
