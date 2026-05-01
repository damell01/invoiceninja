<?php

namespace App\Jobs\Contract;

use App\Models\Contract;
use App\Services\Contract\ContractEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendContractReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly Contract $contract) {}

    public function handle(ContractEmailService $emailService): void
    {
        if (!in_array($this->contract->status, [Contract::STATUS_SENT, Contract::STATUS_VIEWED])) {
            return;
        }

        if ($this->contract->isExpired()) {
            return;
        }

        $emailService->sendReminder($this->contract);
    }
}
