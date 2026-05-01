<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Services\Contract\ContractEmailService;
use App\Services\Contract\ContractService;
use Illuminate\Console\Command;

class ExpireContracts extends Command
{
    protected $signature   = 'contracts:expire';
    protected $description = 'Mark expired contracts and send 48-hour expiration warnings';

    public function handle(ContractService $contractService, ContractEmailService $emailService): void
    {
        // Expire past-due contracts
        $expired = Contract::whereIn('status', [Contract::STATUS_SENT, Contract::STATUS_VIEWED])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $contract) {
            $contractService->expire($contract);
            $this->info("Expired: [{$contract->contract_number}] {$contract->title}");
        }

        // Warn for contracts expiring within 48 hours
        $expiringSoon = Contract::whereIn('status', [Contract::STATUS_SENT, Contract::STATUS_VIEWED])
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addHours(48)])
            ->get();

        foreach ($expiringSoon as $contract) {
            $emailService->sendExpirationWarning($contract);
            $this->info("Warning sent for: [{$contract->contract_number}] {$contract->title}");
        }

        $this->info(sprintf('Done. Expired: %d | Warned: %d', $expired->count(), $expiringSoon->count()));
    }
}
