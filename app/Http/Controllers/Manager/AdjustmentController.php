<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\PendingAdjustment;
use App\Services\AdjustmentService;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Account managers can SUGGEST point adjustments for their assigned
 * agents — every suggestion goes straight into pending_adjustments
 * with requested_by = the manager, awaiting admin approval.
 *
 * Managers cannot apply, approve, or reject — those stay admin-only.
 */
class AdjustmentController extends Controller
{
    public function __construct(private AuditService $audit) {}

    /**
     * Manager's own suggestion history (4 status tabs).
     */
    public function index(Request $request): View
    {
        $manager = $request->user();
        $tab = $request->query('tab', 'pending');

        $query = PendingAdjustment::with(['agent.user', 'approver'])
            ->where('requested_by', $manager->id)
            ->latest();

        if (in_array($tab, ['pending', 'approved', 'rejected', 'cancelled'], true)) {
            $query->where('status', $tab);
        }

        $adjustments = $query->paginate(25)->withQueryString();

        $counts = PendingAdjustment::where('requested_by', $manager->id)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        return view('manager.adjustments.index', compact('adjustments', 'tab', 'counts'));
    }

    /**
     * Form to suggest a new adjustment (always queued, never applied).
     */
    public function store(Request $request, Agent $agent): RedirectResponse
    {
        if ($agent->account_manager_id !== $request->user()->id) {
            abort(404);
        }

        $data = $request->validate([
            'wallet_type'  => ['required', 'in:cash,package'],
            'points_delta' => ['required', 'integer', 'not_in:0'],
            'reason'       => ['required', 'string', 'min:5', 'max:500'],
        ]);

        // Managers can NEVER apply directly — always queue, regardless of size.
        $adjustment = PendingAdjustment::create([
            'agent_id'     => $agent->id,
            'wallet_type'  => $data['wallet_type'],
            'points_delta' => (int) $data['points_delta'],
            'reason'       => $data['reason'],
            'requested_by' => $request->user()->id,
            'status'       => 'pending',
        ]);

        $this->audit->log(
            action: 'adjustment_suggested_by_manager',
            entityType: PendingAdjustment::class,
            entityId: (string) $adjustment->id,
            newValues: $adjustment->getAttributes(),
        );

        return redirect()->route('manager.agents.show', $agent)
            ->with('status', "تم إرسال اقتراح التعديل ({$data['points_delta']} نقطة) للأدمن للموافقة.");
    }

    public function cancel(Request $request, PendingAdjustment $adjustment): RedirectResponse
    {
        if ($adjustment->requested_by !== $request->user()->id) {
            abort(404);
        }

        if ($adjustment->status !== 'pending') {
            return back()->withErrors(['action' => 'لا يمكن إلغاء طلب لم يعد قيد الانتظار.']);
        }

        $adjustment->update(['status' => 'cancelled']);

        $this->audit->log(
            action: 'adjustment_cancelled',
            entityType: PendingAdjustment::class,
            entityId: (string) $adjustment->id,
        );

        return back()->with('status', 'تم إلغاء الاقتراح.');
    }
}
