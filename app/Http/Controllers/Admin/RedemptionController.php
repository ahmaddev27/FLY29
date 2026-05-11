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

        $query = RedemptionRequest::with(['agent.user', 'package', 'processor'])
            ->latest('requested_at');

        if (in_array($status, ['pending', 'approved', 'rejected', 'cancelled', 'fulfilled'], true)) {
            $query->where('status', $status);
        }

        if (in_array($type, ['cash', 'package'], true)) {
            $query->where('type', $type);
        }

        $requests = $query->paginate(25)->withQueryString();

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
}
