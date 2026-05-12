<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\RedemptionRequest;
use App\Services\RedemptionService;
use App\Services\SettingsService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RedemptionController extends Controller
{
    public function __construct(
        private RedemptionService $service,
        private SettingsService $settings,
    ) {}

    /**
     * "طلباتي" — list all redemption requests (cash + package).
     */
    public function index(Request $request): View
    {
        $agent = $request->user()->agent;

        $query = $agent->redemptions()->with('package')->latest('requested_at');

        // Filters
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $perPage  = (int) $request->query('per_page', 25);
        $perPage  = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $requests = $query->paginate($perPage);

        return view('agent.redemptions.index', compact('requests'));
    }

    /**
     * Cash redemption form.
     */
    public function cashForm(Request $request): View
    {
        $agent  = $request->user()->agent;
        $cash   = $agent->cashWallet;
        $config = [
            'available'      => (int) $cash->available_points,
            'locked'         => (int) $cash->locked_points,
            'min_redemption' => (int) $this->settings->get('min_redemption_points', 800),
            'point_value'    => (float) $this->settings->get('point_value_usd', 2.0),
        ];

        return view('agent.redemptions.cash', compact('agent', 'config'));
    }

    /**
     * Submit a cash redemption request.
     */
    public function storeCash(Request $request): RedirectResponse
    {
        $minRedemption = (int) $this->settings->get('min_redemption_points', 800);

        $data = $request->validate([
            'points' => ['required', 'integer', "min:{$minRedemption}"],
        ], [
            'points.min' => "الحد الأدنى للتحويل هو {$minRedemption} نقطة.",
        ]);

        $agent = $request->user()->agent;

        try {
            $redemption = $this->service->createCashRequest($agent, (int) $data['points']);
        } catch (DomainException $e) {
            return back()->withErrors(['points' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('agent.redemptions.index')
            ->with('status', "تم تقديم طلب التحويل برقم #{$redemption->id}. سيُراجعه المدير قريباً.");
    }

    /**
     * Cancel a pending request.
     */
    public function destroy(Request $request, RedemptionRequest $redemption): RedirectResponse
    {
        try {
            $this->service->cancel($redemption, $request->user());
        } catch (DomainException $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }

        return back()->with('status', 'تم إلغاء الطلب واسترداد نقاطك.');
    }
}
