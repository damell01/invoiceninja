<?php

namespace App\Services\Contract;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractAuditLog;
use App\Models\ContractSigner;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ContractService
{
    public function __construct(
        private ContractVariableService $variableService,
        private ContractPdfService $pdfService,
        private ContractEmailService $emailService,
    ) {}

    // ── Create / Update ─────────────────────────────────────────

    public function create(array $data, Company $company, User $user): Contract
    {
        $data['company_id']      = $company->id;
        $data['user_id']         = $user->id;
        $data['public_token']    = Str::random(64);
        $data['contract_number'] = $this->generateContractNumber($company);
        $data['status']          = Contract::STATUS_DRAFT;

        if (!empty($data['contract_body'])) {
            $data['contract_hash'] = hash('sha256', $data['contract_body']);
        }

        $contract = Contract::create($data);

        // Auto-add primary signer from contact or client's primary contact
        if ($contract->contact_id && $contract->contact) {
            $this->addSigner($contract, [
                'name'              => trim(($contract->contact->first_name ?? '') . ' ' . ($contract->contact->last_name ?? '')),
                'email'             => $contract->contact->email,
                'client_contact_id' => $contract->contact_id,
                'signing_order'     => 1,
            ]);
        } elseif ($contract->client_id && $contract->client) {
            $primary = $contract->client->contacts()->first();
            if ($primary) {
                $this->addSigner($contract, [
                    'name'              => trim(($primary->first_name ?? '') . ' ' . ($primary->last_name ?? '')),
                    'email'             => $primary->email,
                    'client_contact_id' => $primary->id,
                    'signing_order'     => 1,
                ]);
            }
        }

        $this->logAudit($contract, ContractAuditLog::EVENT_CREATED, null, Contract::STATUS_DRAFT, 'admin');

        return $contract;
    }

    public function update(Contract $contract, array $data): Contract
    {
        if ($contract->is_locked) {
            throw new \RuntimeException('Cannot edit a locked contract. Create a new version instead.');
        }

        if (!empty($data['contract_body'])) {
            $data['contract_hash'] = hash('sha256', $data['contract_body']);
        }

        $contract->update($data);
        $this->logAudit($contract, ContractAuditLog::EVENT_EDITED, null, $contract->status, 'admin');

        return $contract->fresh();
    }

    public function createNewVersion(Contract $original): Contract
    {
        $new = $original->replicate(['signed_at', 'viewed_at', 'declined_at', 'sent_at', 'pdf_path', 'signed_pdf_path']);
        $new->parent_id       = $original->id;
        $new->version         = $original->version + 1;
        $new->status          = Contract::STATUS_DRAFT;
        $new->is_locked       = false;
        $new->public_token    = Str::random(64);
        $new->contract_number = $original->contract_number . '-v' . ($original->version + 1);
        $new->save();

        $this->logAudit($new, ContractAuditLog::EVENT_CREATED, null, Contract::STATUS_DRAFT, 'admin',
            'New version created from v' . $original->version);

        return $new;
    }

    // ── Signers ────────────────────────────────────────────────

    public function addSigner(Contract $contract, array $data): ContractSigner
    {
        $data['contract_id'] = $contract->id;
        $data['token']       = Str::random(64);
        $data['status']      = ContractSigner::STATUS_PENDING;
        return ContractSigner::create($data);
    }

    // ── Lifecycle ──────────────────────────────────────────────

    public function send(Contract $contract, array $options = []): Contract
    {
        if ($contract->status === Contract::STATUS_SIGNED) {
            throw new \RuntimeException('Cannot resend a fully signed contract.');
        }

        $oldStatus = $contract->status;
        $contract->update([
            'status'  => Contract::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $this->logAudit($contract, ContractAuditLog::EVENT_SENT, $oldStatus, Contract::STATUS_SENT, 'admin');
        $this->emailService->sendContractToSigners($contract, $options);

        return $contract->fresh();
    }

    public function markViewed(Contract $contract, string $ip, string $userAgent): void
    {
        if (!$contract->viewed_at) {
            $contract->update([
                'status'    => Contract::STATUS_VIEWED,
                'viewed_at' => now(),
            ]);
        }
        $this->logAudit($contract, ContractAuditLog::EVENT_VIEWED, null, $contract->status, 'client', null, $ip, $userAgent);
    }

    public function sign(Contract $contract, ContractSigner $signer, array $signatureData): Contract
    {
        if ($contract->isExpired()) {
            throw new \RuntimeException('This contract has expired and cannot be signed.');
        }

        if ($contract->status === Contract::STATUS_SIGNED) {
            throw new \RuntimeException('This contract has already been fully signed.');
        }

        if ($signer->isSigned()) {
            throw new \RuntimeException('You have already signed this contract.');
        }

        $signer->update([
            'status'         => ContractSigner::STATUS_SIGNED,
            'signed_at'      => now(),
            'signature_data' => $signatureData['signature_data'],
            'signature_type' => $signatureData['signature_type'] ?? 'drawn',
            'signed_name'    => $signatureData['signed_name'],
            'signed_title'   => $signatureData['signed_title'] ?? null,
            'ip_address'     => $signatureData['ip_address'],
            'user_agent'     => $signatureData['user_agent'],
        ]);

        $this->logAudit(
            $contract,
            ContractAuditLog::EVENT_SIGNED,
            $contract->status,
            null,
            'signer',
            'Signed by: ' . $signer->name . ' <' . $signer->email . '>',
            $signatureData['ip_address'],
            $signatureData['user_agent'],
            $signer->id,
        );

        // Refresh to get updated signer data
        $contract->refresh();

        if ($contract->allSignersSigned()) {
            if ($contract->requires_counter_signature) {
                // Await internal counter-signature
                $this->logAudit($contract, ContractAuditLog::EVENT_STATUS_CHANGED, null, 'awaiting_counter_signature', 'system', 'All client signers have signed. Awaiting counter-signature.');
            } else {
                $this->finalizeSigned($contract);
            }
        }

        return $contract->fresh();
    }

    public function counterSign(Contract $contract, User $user): Contract
    {
        $contract->update([
            'status'             => Contract::STATUS_SIGNED,
            'signed_at'          => now(),
            'counter_signed_at'  => now(),
            'counter_signed_by'  => $user->id,
            'is_locked'          => true,
        ]);

        $this->logAudit($contract, ContractAuditLog::EVENT_COUNTER_SIGNED, null, Contract::STATUS_SIGNED, 'admin');
        $this->generateSignedPdf($contract);
        $this->emailService->sendSignedConfirmation($contract);

        return $contract->fresh();
    }

    public function decline(Contract $contract, ContractSigner $signer, string $reason = ''): void
    {
        $signer->update([
            'status'         => ContractSigner::STATUS_DECLINED,
            'declined_at'    => now(),
            'decline_reason' => $reason,
        ]);

        $oldStatus = $contract->status;
        $contract->update([
            'status'      => Contract::STATUS_DECLINED,
            'declined_at' => now(),
        ]);

        $this->logAudit($contract, ContractAuditLog::EVENT_DECLINED, $oldStatus, Contract::STATUS_DECLINED, 'signer',
            'Declined by: ' . $signer->name . '. Reason: ' . $reason);

        $this->emailService->sendDeclinedNotification($contract, $signer, $reason);
    }

    public function approve(Contract $contract, User $user): Contract
    {
        $oldStatus = $contract->status;
        $contract->update(['status' => Contract::STATUS_APPROVED]);
        $this->logAudit($contract, ContractAuditLog::EVENT_APPROVED, $oldStatus, Contract::STATUS_APPROVED, 'admin');
        return $contract->fresh();
    }

    public function cancel(Contract $contract): Contract
    {
        $oldStatus = $contract->status;
        $contract->update(['status' => Contract::STATUS_CANCELLED]);
        $this->logAudit($contract, ContractAuditLog::EVENT_CANCELLED, $oldStatus, Contract::STATUS_CANCELLED, 'admin');
        return $contract->fresh();
    }

    public function expire(Contract $contract): void
    {
        $oldStatus = $contract->status;
        $contract->update(['status' => Contract::STATUS_EXPIRED]);
        $this->logAudit($contract, ContractAuditLog::EVENT_EXPIRED, $oldStatus, Contract::STATUS_EXPIRED, 'system');
    }

    // ── PDF ─────────────────────────────────────────────────────

    public function generatePdf(Contract $contract): string
    {
        $path = $this->pdfService->generate($contract, false);
        $contract->update(['pdf_path' => $path]);
        $this->logAudit($contract, ContractAuditLog::EVENT_PDF_GENERATED, null, null, 'admin');
        return $path;
    }

    public function generateSignedPdf(Contract $contract): string
    {
        $path = $this->pdfService->generate($contract, true);
        $contract->update(['signed_pdf_path' => $path]);
        return $path;
    }

    // ── Private helpers ────────────────────────────────────────

    private function finalizeSigned(Contract $contract): void
    {
        $contract->update([
            'status'    => Contract::STATUS_SIGNED,
            'signed_at' => now(),
            'is_locked' => true,
        ]);

        $this->logAudit($contract, ContractAuditLog::EVENT_STATUS_CHANGED, null, Contract::STATUS_SIGNED, 'system', 'All parties signed');
        $this->generateSignedPdf($contract);
        $this->emailService->sendSignedConfirmation($contract);
    }

    private function generateContractNumber(Company $company): string
    {
        $count  = Contract::where('company_id', $company->id)->withTrashed()->count() + 1;
        $prefix = $company->settings->contract_number_prefix ?? 'CON-';
        return $prefix . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    private function logAudit(
        Contract $contract,
        string   $event,
        ?string  $oldStatus  = null,
        ?string  $newStatus  = null,
        string   $actorType  = 'system',
        ?string  $notes      = null,
        ?string  $ipAddress  = null,
        ?string  $userAgent  = null,
        ?int     $signerId   = null,
    ): void {
        ContractAuditLog::create([
            'contract_id' => $contract->id,
            'user_id'     => Auth::id(),
            'signer_id'   => $signerId,
            'event'       => $event,
            'old_status'  => $oldStatus,
            'new_status'  => $newStatus,
            'ip_address'  => $ipAddress ?? request()->ip(),
            'user_agent'  => $userAgent ?? request()->userAgent(),
            'actor_type'  => $actorType,
            'notes'       => $notes,
        ]);
    }
}
