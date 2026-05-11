<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

/**
 * Builds the transaction history query with all supported filters and sorts.
 *
 * Returning a `Builder` means controllers can choose between paginate(),
 * chunk() (for streaming exports), or get() depending on context.
 */
class TransactionQueryService
{
    /** @var array<string,string> sortable column → DB column */
    public const SORT_COLUMNS = [
        'date'   => 'transaction_date',
        'amount' => 'amount_usd',
        'points' => 'points_awarded',
    ];

    public function forAgent(Agent $agent, array $filters = []): Builder
    {
        $q = Transaction::query()->where('agent_id', $agent->id);

        // Date range
        if (! empty($filters['from'])) {
            $q->where('transaction_date', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $q->where('transaction_date', '<=', $filters['to'] . ' 23:59:59');
        }

        // Type
        if (! empty($filters['type']) && in_array($filters['type'], ['package', 'service'], true)) {
            $q->where('transaction_type', $filters['type']);
        }

        // Search by reference_id
        if (! empty($filters['reference'])) {
            $q->where('reference_id', 'like', '%' . $filters['reference'] . '%');
        }

        // Sort
        $sort = $filters['sort']    ?? 'date';
        $dir  = $filters['dir']     ?? 'desc';
        $col  = self::SORT_COLUMNS[$sort] ?? 'transaction_date';
        $dir  = in_array($dir, ['asc', 'desc'], true) ? $dir : 'desc';

        return $q->orderBy($col, $dir);
    }
}
