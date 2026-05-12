<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TopAgentsReportExport;
use App\Http\Controllers\Controller;
use App\Services\PdfService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(private ReportService $reports) {}

    /**
     * Reports landing — shows links + range picker.
     */
    public function index(): View
    {
        return view('admin.reports.index');
    }

    public function points(Request $request): View
    {
        $data = $this->reports->pointsReport($request->only('from', 'to'));

        return view('admin.reports.points', $data);
    }

    public function sales(Request $request): View
    {
        $data = $this->reports->salesReport($request->only('from', 'to'));

        return view('admin.reports.sales', $data);
    }

    public function tiers(Request $request): View
    {
        $data = $this->reports->tiersReport($request->only('from', 'to'));

        return view('admin.reports.tiers', $data);
    }

    public function redemptions(Request $request): View
    {
        $data = $this->reports->redemptionsReport($request->only('from', 'to'));

        return view('admin.reports.redemptions', $data);
    }

    public function topAgents(Request $request): View
    {
        $orderBy = $request->query('order_by', 'revenue');
        $data    = $this->reports->topAgentsReport($request->only('from', 'to'), $orderBy);

        return view('admin.reports.top-agents', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Exports
    |--------------------------------------------------------------------------
    */

    public function topAgentsExcel(Request $request): BinaryFileResponse
    {
        $orderBy = $request->query('order_by', 'revenue');
        $data    = $this->reports->topAgentsReport($request->only('from', 'to'), $orderBy);

        return Excel::download(
            new TopAgentsReportExport($data['agents'], $data['range'], $orderBy),
            'top-agents-' . now()->format('Y-m-d-His') . '.xlsx',
        );
    }

    public function salesPdf(Request $request, PdfService $pdf): Response
    {
        $data = $this->reports->salesReport($request->only('from', 'to'));

        $html = view('admin.reports.pdf.sales', $data)->render();

        return $pdf->downloadArabic($html, 'sales-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function pointsPdf(Request $request, PdfService $pdf): Response
    {
        $data = $this->reports->pointsReport($request->only('from', 'to'));

        $html = view('admin.reports.pdf.points', $data)->render();

        return $pdf->downloadArabic($html, 'points-' . now()->format('Y-m-d-His') . '.pdf');
    }
}
