<?php

namespace App\Services\Contract;

use App\Mail\Contract\ContractExpiredMail;
use App\Mail\Contract\ContractReminderMail;
use App\Mail\Contract\ContractSentMail;
use App\Mail\Contract\ContractSignedMail;
use App\Models\Contract;
use App\Models\ContractSigner;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContractEmailService
{
    public function sendContractToSigners(Contract $contract, array $options = []): void
    {
        $subject = $options['subject'] ?? 'Contract Ready for Signature: ' . $contract->title;
        $message = $options['message'] ?? '';

        foreach ($contract->signers()->where('status', 'pending')->get() as $signer) {
            $this->trySend($signer->email, new ContractSentMail($contract, $signer, $subject, $message));
        }
    }

    public function sendSignedConfirmation(Contract $contract): void
    {
        // Notify admin user
        if ($contract->user?->email) {
            $this->trySend($contract->user->email, new ContractSignedMail($contract, null, true));
        }

        // Send signed copy to every signer
        foreach ($contract->signers as $signer) {
            $this->trySend($signer->email, new ContractSignedMail($contract, $signer, false));
        }
    }

    public function sendReminder(Contract $contract): void
    {
        foreach ($contract->signers()->where('status', 'pending')->get() as $signer) {
            $this->trySend($signer->email, new ContractReminderMail($contract, $signer));
        }
    }

    public function sendExpirationWarning(Contract $contract): void
    {
        foreach ($contract->signers()->where('status', 'pending')->get() as $signer) {
            $this->trySend($signer->email, new ContractExpiredMail($contract, $signer, true));
        }

        if ($contract->user?->email) {
            $this->trySend($contract->user->email, new ContractExpiredMail($contract, null, false));
        }
    }

    public function sendDeclinedNotification(Contract $contract, ContractSigner $signer, string $reason): void
    {
        if ($contract->user?->email) {
            $this->trySend($contract->user->email, new ContractExpiredMail($contract, $signer, false));
        }
    }

    private function trySend(string $to, object $mailable): void
    {
        try {
            Mail::to($to)->send($mailable);
        } catch (\Exception $e) {
            Log::error('[ContractEmailService] Failed to send ' . get_class($mailable) . ' to ' . $to . ': ' . $e->getMessage());
        }
    }
}
