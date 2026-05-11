<?php

namespace App\Services;

use App\Models\Transaction;

class IdempotencyService
{
    /**
     * Check if a transaction with this reference_id already exists.
     * Returns the existing Transaction or null.
     */
    public function findExisting(string $referenceId): ?Transaction
    {
        return Transaction::where('reference_id', $referenceId)->first();
    }

    public function isDuplicate(string $referenceId): bool
    {
        return Transaction::where('reference_id', $referenceId)->exists();
    }
}
