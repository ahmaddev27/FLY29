<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\PendingAdjustment;
use App\Models\RedemptionRequest;
use App\Models\Transaction;
use App\Models\User;

/**
 * KPIs + roll-ups for the account-manager dashboard.
 *
 * Every query filters by $manager->id — managers only see their own
 * assigned agents. Returns a typed dictionary the view can render
 * without any additional logic.
 *
 * @return array<string, mixed>
 */
class ManagerDashboardService
{
    public function aggregate(User $manager): array
    {
        $agentIds = Agent::where('account_manager_id', $manager->id)->pluck('id');

        $monthStart = now()->startOfMonth();

        $kpis = [
            'total_agents'    => $agentIds->count(),
            'active_agents'   => Agent::whereIn('id', $agentIds)
                ->whereHas('user', fn ($q) => $q->where('status', 'active'))
                ->count(),
            'monthly_txns'    => Transaction::whereIn('agent_id', $agentIds)
                ->where('transaction_date', '>=', $monthStart)->count(),
            'monthly_revenue' => Transaction::whereIn('agent_id', $agentIds)
                ->where('transaction_date', '>=', $monthStart)
                ->sum('amount_usd'),
            'pending_redemptions' => RedemptionRequest::whereIn('agent_id', $agentIds)
                ->where('status', 'pending')->count(),
            'pending_adjustments' => PendingAdjustment::whereIn('agent_id', $agentIds)
                ->where('requested_by', $manager->id)
                ->where('status', 'pending')->count(),
        ];

        $topAgents = Agent::whereIn('id', $agentIds)
            ->with('user')
            ->withCount(['transactions as monthly_txns' => fn ($q) => $q->where('transaction_date', '>=', $monthStart)])
            ->withSum(['transactions as monthly_revenue' => fn ($q) => $q->where('transaction_date', '>=', $monthStart)], 'amount_usd')
            ->orderByDesc('monthly_revenue')
            ->limit(5)
            ->get();

        $tierBreakdown = Agent::whereIn('id', $agentIds)
            ->selectRaw('current_tier, COUNT(*) as c')
            ->groupBy('current_tier')
            ->pluck('c', 'current_tier')
            ->toArray();

        $tierBreakdown = array_merge(
            ['bronze' => 0, 'silver' => 0, 'gold' => 0, 'diamond' => 0],
            $tierBreakdown,
        );

        return [
            'kpis'           => $kpis,
            'top_agents'     => $topAgents,
            'tier_breakdown' => $tierBreakdown,
        ];
    }
}
