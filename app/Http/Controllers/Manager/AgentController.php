<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentController extends Controller
{
    /**
     * Lists only this manager's assigned agents.
     */
    public function index(Request $request): View
    {
        $manager = $request->user();

        $query = Agent::where('account_manager_id', $manager->id)
            ->with(['user', 'cashWallet', 'packageWallet'])
            ->latest();

        if ($q = $request->query('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('business_name', 'like', "%{$q}%")
                   ->orWhere('external_agent_id', 'like', "%{$q}%")
                   ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$q}%"));
            });
        }

        if ($tier = $request->query('tier')) {
            $query->where('current_tier', $tier);
        }

        $agents = $query->paginate(25)->withQueryString();

        return view('manager.agents.index', compact('agents'));
    }

    /**
     * Read-only profile view — manager can see everything but only suggest
     * adjustments via the dedicated form. Throws 404 for agents not owned
     * by this manager.
     */
    public function show(Request $request, Agent $agent): View
    {
        $this->authorizeOwnership($request, $agent);

        $agent->load(['user', 'cashWallet', 'packageWallet', 'tierLevel']);

        $recentTxns        = $agent->transactions()->latest('transaction_date')->limit(15)->get();
        $recentRedemptions = $agent->redemptions()->with('package')->latest('requested_at')->limit(10)->get();
        $tierHistory       = $agent->tierHistory()->orderByDesc('created_at')->limit(5)->get();

        return view('manager.agents.show', compact('agent', 'recentTxns', 'recentRedemptions', 'tierHistory'));
    }

    /**
     * 404 if the agent isn't assigned to the calling manager.
     * Treating it as 404 (not 403) avoids leaking the existence of agents
     * the manager has no business knowing about.
     */
    private function authorizeOwnership(Request $request, Agent $agent): void
    {
        if ($agent->account_manager_id !== $request->user()->id) {
            abort(404);
        }
    }
}
