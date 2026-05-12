<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TopAgentsReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        private Collection $agents,
        private array $range,
        private string $orderBy,
    ) {}

    public function collection(): Collection
    {
        return $this->agents->map(fn ($a, $i) => [
            'rank'            => $i + 1,
            'business_name'   => $a->business_name,
            'external_id'     => $a->external_agent_id,
            'tier'            => $a->current_tier,
            'country'         => $a->country,
            'transactions'    => (int) ($a->period_txns ?? 0),
            'revenue_usd'     => round((float) ($a->period_revenue ?? 0), 2),
            'points'          => (int) ($a->period_points ?? 0),
        ]);
    }

    public function headings(): array
    {
        return [
            '#', 'الاسم التجاري', 'ID', 'التصنيف', 'الدولة',
            'عدد المعاملات', 'الإيرادات (USD)', 'النقاط',
        ];
    }

    public function title(): string
    {
        return "Top Agents · {$this->range['from']} → {$this->range['to']}";
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F1F5F9']]],
        ];
    }
}
