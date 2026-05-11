<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Excel/CSV export driven by a prepared Builder so it shares the exact
 * filters the user sees on the page. Uses chunked reads via FromQuery
 * to keep memory flat on large datasets.
 */
class TransactionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'التاريخ',
            'الوقت',
            'النوع',
            'الوجهة',
            'المبلغ (USD)',
            'النقاط',
            'رقم المرجع',
        ];
    }

    public function map($row): array
    {
        /** @var Transaction $row */
        return [
            $row->transaction_date->format('Y-m-d'),
            $row->transaction_date->format('H:i'),
            $row->transaction_type === 'package' ? 'باكج' : 'خدمة',
            $row->destination ?? '—',
            number_format((float) $row->amount_usd, 2),
            $row->points_awarded,
            $row->reference_id,
        ];
    }

    public function title(): string
    {
        return 'سجل النقاط';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->setRightToLeft(true);

        return [
            1 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']],
        ];
    }
}
