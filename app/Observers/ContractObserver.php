<?php

namespace App\Observers;

use App\Models\Contract;
use Illuminate\Support\Str;

class ContractObserver
{
    public function creating(Contract $contract): void
    {
        if (empty($contract->public_token)) {
            $contract->public_token = Str::random(64);
        }
    }

    public function updating(Contract $contract): void
    {
        if ($contract->isDirty('contract_body') && $contract->contract_body) {
            $contract->contract_hash = hash('sha256', $contract->contract_body);
        }
    }
}
