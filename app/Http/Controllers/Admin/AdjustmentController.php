<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\PendingAdjustment;
use App\Services\AdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdjustmentController extends Controller
{
    public function __construct(private AdjustmentService $adjustments) {}

    /**
     * Pending approval queue (for super_admin) + recent history.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'pending');

        $query = PendingAdjustment::with(['agent.user', 'requester', 'approver'])
            ->latest();

        if (in_array($tab, ['pending', 'approved', 'rejected', 'cancelled'], true)) {
            $query->where('status', $tab);
        }

        $adjustments = $query->paginate(25)->withQueryString();

        $counts = PendingAdjustment::selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        return view('admin.adjustments.index', compact('adjustments', 'tab', 'counts'));
    }

    /**
     * Submit a manual adjustment from the agent profile page.
     */
    public function store(Request $request, Agent $agent): RedirectResponse
    {
        $data = $request->validate([
            'wallet_type'  => ['required', 'in:cash,package'],
            'points_delta' => ['required', 'integer', 'not_in:0'],
            'reason'       => ['required', 'string', 'min:5', 'max:500'],
        ]);

        try {
            $result = $this->adjustments->request(
                agent: $agent,
                wallet: $data['wallet_type'],
                delta: (int) $data['points_delta'],
                reason: $data['reason'],
                requestedBy: $request->user(),
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['points_delta' => $e->getMessage()])->withInput();
        }

        return back()->with(
            'status',
            $result['applied']
                ? "تم تطبيق التعديل (الرصيد الجديد: {$result['balance']} نقطة)."
                : "التعديل ({$data['points_delta']} نقطة) أكبر من الحد المسموح — أُرسل للموافقة المزدوجة."
        );
    }

    public function approve(Request $request, PendingAdjustment $adjustment): RedirectResponse
    {
        if (! $request->user()->isSuperAdmin()) {
            abort(403, 'الموافقة على التعديلات المزدوجة متاحة للسوبر أدمن فقط.');
        }

        $notes = $request->validate(['notes' => ['nullable', 'string', 'max:500']])['notes'] ?? null;

        try {
            $balance = $this->adjustments->approve($adjustment, $request->user(), $notes);
        } catch (\DomainException $e) {
            return back()->withErrors(['adjustment' => $e->getMessage()]);
        }

        return back()->with('status', "تم اعتماد التعديل — الرصيد الجديد: {$balance} نقطة.");
    }

    public function reject(Request $request, PendingAdjustment $adjustment): RedirectResponse
    {
        if (! $request->user()->isSuperAdmin()) {
            abort(403, 'الرفض متاح للسوبر أدمن فقط.');
        }

        $data = $request->validate([
            'notes' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        try {
            $this->adjustments->reject($adjustment, $request->user(), $data['notes']);
        } catch (\DomainException $e) {
            return back()->withErrors(['adjustment' => $e->getMessage()]);
        }

        return back()->with('status', 'تم رفض التعديل.');
    }

    public function cancel(Request $request, PendingAdjustment $adjustment): RedirectResponse
    {
        try {
            $this->adjustments->cancel($adjustment, $request->user());
        } catch (\DomainException $e) {
            return back()->withErrors(['adjustment' => $e->getMessage()]);
        }

        return back()->with('status', 'تم إلغاء طلب التعديل.');
    }
}
