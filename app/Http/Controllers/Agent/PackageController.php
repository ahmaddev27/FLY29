<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\FreePackage;
use App\Services\RedemptionService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(private RedemptionService $redemptions) {}

    /**
     * List active free packages, ordered by display_order then cost.
     */
    public function index(Request $request): View
    {
        $agent    = $request->user()->agent;
        $balance  = (int) ($agent->packageWallet?->available_points ?? 0);

        $packages = FreePackage::active()
            ->orderBy('display_order')
            ->orderBy('points_required')
            ->get()
            ->map(function (FreePackage $pkg) use ($balance) {
                $pkg->affordable = $balance >= $pkg->points_required;
                $pkg->missing    = max(0, $pkg->points_required - $balance);

                return $pkg;
            });

        return view('agent.packages.index', [
            'packages' => $packages,
            'balance'  => $balance,
        ]);
    }

    /**
     * Redeem a package — instant flow, points debited immediately.
     */
    public function redeem(Request $request, FreePackage $package): RedirectResponse
    {
        $agent = $request->user()->agent;

        try {
            $redemption = $this->redemptions->redeemPackage($agent, $package);
        } catch (DomainException $e) {
            return back()->withErrors(['redeem' => $e->getMessage()]);
        }

        return redirect()
            ->route('agent.redemptions.index')
            ->with('status', "تم استبدال نقاطك بـ «{$package->name}». سيتم التواصل معك قريباً لإكمال التنفيذ.");
    }
}
