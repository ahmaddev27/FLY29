<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RedemptionRequest;
use App\Services\RedemptionService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RedemptionController extends Controller
{
    public function __construct(private RedemptionService $service) {}

    /**
     * List redemption requests (filter by status).
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');
        $type   = $request->query('type');
        $search = $request->query('q');

        $query = RedemptionRequest::with(['agent.user', 'package', 'processor'])
            ->latest('requested_at');

        if (in_array($status, ['pending', 'approved', 'rejected', 'cancelled', 'fulfilled'], true)) {
            $query->where('status', $status);
        }

        if (in_array($type, ['cash', 'package'], true)) {
            $query->where('type', $type);
        }

        if ($search) {
            $query->whereHas('agent', function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('external_agent_id', 'like', "%{$search}%");
            });
        }

        $perPage  = (int) $request->query('per_page', 25);
        $perPage  = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $requests = $query->paginate($perPage);

        $counts = [
            'pending'   => RedemptionRequest::where('status', 'pending')->count(),
            'approved'  => RedemptionRequest::where('status', 'approved')->count(),
            'rejected'  => RedemptionRequest::where('status', 'rejected')->count(),
            'cancelled' => RedemptionRequest::where('status', 'cancelled')->count(),
        ];

        return view('admin.redemptions.index', [
            'requests'      => $requests,
            'currentStatus' => $status,
            'currentType'   => $type,
            'counts'        => $counts,
        ]);
    }

    public function approve(Request $request, RedemptionRequest $redemption): RedirectResponse
    {
        try {
            $this->service->approveCash($redemption, $request->user());
        } catch (DomainException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }

        return back()->with('status', "تمت الموافقة على الطلب #{$redemption->id}.");
    }

    public function reject(Request $request, RedemptionRequest $redemption): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'rejection_reason.required' => 'سبب الرفض مطلوب.',
            'rejection_reason.min'      => 'السبب يجب أن يكون 5 أحرف على الأقل.',
        ]);

        try {
            $this->service->rejectCash($redemption, $request->user(), $data['rejection_reason']);
        } catch (DomainException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }

        return back()->with('status', "تم رفض الطلب #{$redemption->id} مع إبلاغ الوكيل.");
    }

    /**
     * Mark an approved redemption as fulfilled (paid/booked).
     */
    public function fulfill(Request $request, RedemptionRequest $redemption): RedirectResponse
    {
        $isCash = $redemption->type === 'cash';

        $data = $request->validate([
            'fulfillment_reference' => [$isCash ? 'required' : 'nullable', 'string', 'max:150'],
            'fulfillment_notes'     => ['nullable', 'string', 'max:1000'],
        ], [
            'fulfillment_reference.required' => 'رقم مرجع التحويل البنكي مطلوب للنقدي.',
        ]);

        try {
            $this->service->fulfill(
                $redemption,
                $request->user(),
                $data['fulfillment_reference'] ?? null,
                $data['fulfillment_notes']     ?? null,
            );
        } catch (DomainException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }

        $label = $isCash ? 'تم تأكيد التحويل البنكي' : 'تم تأكيد حجز الباكج';

        return back()->with('status', "{$label} للطلب #{$redemption->id}.");
    }

    /**
     * Reverse a fulfillment (e.g. payment bounced and needs to be reissued).
     */
    public function reverseFulfillment(Request $request, RedemptionRequest $redemption): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        try {
            $this->service->reverseFulfillment($redemption, $request->user(), $data['reason']);
        } catch (DomainException $e) {
            return back()->withErrors(['action' => $e->getMessage()]);
        }

        return back()->with('status', "تم عكس تنفيذ الطلب #{$redemption->id} — أصبح بحاجة لإعادة التنفيذ.");
    }

    /**
     * Bulk approve — only cash requests in pending status.
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:redemption_requests,id'],
        ]);

        $pending = RedemptionRequest::whereIn('id', $data['ids'])
            ->where('status', 'pending')
            ->where('type', 'cash')
            ->get();

        $ok = 0; $fail = 0;

        foreach ($pending as $redemption) {
            try {
                $this->service->approveCash($redemption, $request->user());
                $ok++;
            } catch (DomainException $e) {
                $fail++;
            }
        }

        return back()->with(
            'status',
            "تمت الموافقة على {$ok} طلب" . ($fail ? " — تخطّى {$fail} طلب (خطأ أو غير مؤهل)." : '.')
        );
    }

    /**
     * Bulk reject — requires one shared reason for all selected pending requests.
     */
    public function bulkReject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'              => ['required', 'array', 'min:1'],
            'ids.*'            => ['integer', 'exists:redemption_requests,id'],
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $pending = RedemptionRequest::whereIn('id', $data['ids'])
            ->where('status', 'pending')
            ->where('type', 'cash')
            ->get();

        $ok = 0; $fail = 0;

        foreach ($pending as $redemption) {
            try {
                $this->service->rejectCash($redemption, $request->user(), $data['rejection_reason']);
                $ok++;
            } catch (DomainException $e) {
                $fail++;
            }
        }

        return back()->with(
            'status',
            "تم رفض {$ok} طلب" . ($fail ? " — تخطّى {$fail} طلب." : '.')
        );
    }
}
