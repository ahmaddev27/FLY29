<?php

namespace App\Http\Controllers\Agent;

use App\Exports\TransactionsExport;
use App\Http\Controllers\Controller;
use App\Services\PdfService;
use App\Services\TransactionQueryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    /** Hard limit for synchronous exports; beyond this we stream / chunk. */
    private const STREAM_THRESHOLD = 5000;

    public function __construct(private TransactionQueryService $queries) {}

    /**
     * Transaction history page with filters + pagination.
     */
    public function index(Request $request): View
    {
        $agent   = $request->user()->agent;
        $filters = $this->parseFilters($request);

        $transactions = $this->queries
            ->forAgent($agent, $filters)
            ->paginate(50)
            ->withQueryString();

        // Aggregates for the summary card (across all filtered rows, not just the page)
        $summary = $this->queries->forAgent($agent, $filters)
            ->selectRaw('COUNT(*) as txn_count, COALESCE(SUM(points_awarded),0) as total_points, COALESCE(SUM(amount_usd),0) as total_amount')
            ->first();

        return view('agent.transactions.index', compact('transactions', 'filters', 'summary'));
    }

    /**
     * Streamed CSV export.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $agent   = $request->user()->agent;
        $filters = $this->parseFilters($request);
        $query   = $this->queries->forAgent($agent, $filters);

        $filename = 'transactions-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Arabic shows correctly in Excel
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['التاريخ', 'الوقت', 'النوع', 'الوجهة', 'المبلغ USD', 'النقاط', 'رقم المرجع']);

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row->transaction_date->format('Y-m-d'),
                        $row->transaction_date->format('H:i'),
                        $row->transaction_type === 'package' ? 'باكج' : 'خدمة',
                        $row->destination ?? '—',
                        number_format((float) $row->amount_usd, 2, '.', ''),
                        $row->points_awarded,
                        $row->reference_id,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Excel (.xlsx) export.
     */
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $agent   = $request->user()->agent;
        $filters = $this->parseFilters($request);
        $query   = $this->queries->forAgent($agent, $filters);

        $filename = 'transactions-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new TransactionsExport($query), $filename);
    }

    /**
     * PDF export (RTL).
     */
    public function exportPdf(Request $request, PdfService $pdf): Response
    {
        $agent   = $request->user()->agent;
        $filters = $this->parseFilters($request);
        $query   = $this->queries->forAgent($agent, $filters);

        $transactions = $query->limit(self::STREAM_THRESHOLD)->get();

        $html = view('agent.transactions.pdf', [
            'agent'        => $agent,
            'transactions' => $transactions,
            'filters'      => $filters,
            'generatedAt'  => now(),
        ])->render();

        return $pdf->downloadArabic($html, 'transactions-' . now()->format('Y-m-d-His') . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    private function parseFilters(Request $request): array
    {
        return array_filter([
            'from'      => $request->query('from'),
            'to'        => $request->query('to'),
            'type'      => $request->query('type'),
            'reference' => $request->query('reference'),
            'sort'      => $request->query('sort', 'date'),
            'dir'       => $request->query('dir', 'desc'),
        ], fn ($v) => $v !== null && $v !== '');
    }
}
