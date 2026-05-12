<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير المبيعات</title>
    <style>
        body { font-family: xbriyaz, sans-serif; font-size: 11px; color: #222; }
        h1 { margin: 0 0 4px; font-size: 20px; color: #0066CC; font-weight: bold; }
        .meta { font-size: 10px; color: #666; margin-bottom: 16px; }
        .kpis { display: table; width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .kpi { display: table-cell; padding: 12px; background: #F8FAFC; border: 1px solid #E5E7EB; width: 25%; }
        .kpi-label { font-size: 10px; color: #6B7280; }
        .kpi-value { font-size: 16px; font-weight: bold; color: #0F172A; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: right; font-size: 10px; }
        th { background: #f5f5f5; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 9px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h1>تقرير المبيعات</h1>
    <div class="meta">
        <div>الفترة: {{ $range['from'] }} → {{ $range['to'] }}</div>
        <div>تاريخ التوليد: {{ now()->format('Y-m-d H:i') }}</div>
    </div>

    <div class="kpis">
        <div class="kpi">
            <div class="kpi-label">عدد المعاملات</div>
            <div class="kpi-value">{{ number_format($totals['count']) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">إجمالي الإيرادات</div>
            <div class="kpi-value">${{ number_format($totals['revenue'], 0) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">متوسط القيمة</div>
            <div class="kpi-value">${{ number_format($totals['avg_value'], 0) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">باكجات / خدمات</div>
            <div class="kpi-value">{{ $totals['packages'] }} / {{ $totals['services'] }}</div>
        </div>
    </div>

    <h2 style="font-size:14px;margin-top:18px;">التفصيل اليومي</h2>
    <table>
        <thead>
            <tr>
                <th>اليوم</th>
                <th>عدد المعاملات</th>
                <th>الإيرادات (USD)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($series as $d)
                @if($d['count'] > 0)
                <tr>
                    <td>{{ $d['day'] }}</td>
                    <td>{{ number_format($d['count']) }}</td>
                    <td>${{ number_format($d['revenue'], 2) }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="footer">29FLY Loyalty Program — تم توليد هذا التقرير تلقائياً</div>
</body>
</html>
