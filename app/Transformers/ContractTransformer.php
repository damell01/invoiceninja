<?php

namespace App\Transformers;

use App\Models\Contract;
use League\Fractal\TransformerAbstract;

class ContractTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['signers', 'audit_logs'];

    public function transform(Contract $contract): array
    {
        return [
            'id'                         => $contract->id,
            'company_id'                 => $contract->company_id,
            'client_id'                  => $contract->client_id,
            'contact_id'                 => $contract->contact_id,
            'invoice_id'                 => $contract->invoice_id,
            'quote_id'                   => $contract->quote_id,
            'project_id'                 => $contract->project_id,
            'user_id'                    => $contract->user_id,
            'assigned_user_id'           => $contract->assigned_user_id,
            'template_id'                => $contract->template_id,
            'parent_id'                  => $contract->parent_id,
            'contract_number'            => $contract->contract_number,
            'title'                      => $contract->title,
            'status'                     => $contract->status,
            'contract_body'              => $contract->contract_body,
            'public_token'               => $contract->public_token,
            'public_signing_url'         => $contract->getPublicSigningUrl(),
            'expires_at'                 => $contract->expires_at?->toIso8601String(),
            'sent_at'                    => $contract->sent_at?->toIso8601String(),
            'viewed_at'                  => $contract->viewed_at?->toIso8601String(),
            'signed_at'                  => $contract->signed_at?->toIso8601String(),
            'declined_at'                => $contract->declined_at?->toIso8601String(),
            'contract_hash'              => $contract->contract_hash,
            'notes'                      => $contract->notes,
            'footer'                     => $contract->footer,
            'requires_counter_signature' => $contract->requires_counter_signature,
            'counter_signed_at'          => $contract->counter_signed_at?->toIso8601String(),
            'is_locked'                  => $contract->is_locked,
            'version'                    => $contract->version,
            'has_pdf'                    => !empty($contract->pdf_path),
            'has_signed_pdf'             => !empty($contract->signed_pdf_path),
            'is_expired'                 => $contract->isExpired(),
            'is_editable'                => $contract->isEditable(),
            'signers_count'              => $contract->signers()->count(),
            'signers_signed_count'       => $contract->signers()->where('status', 'signed')->count(),
            'created_at'                 => $contract->created_at?->toIso8601String(),
            'updated_at'                 => $contract->updated_at?->toIso8601String(),
            'deleted_at'                 => $contract->deleted_at?->toIso8601String(),
        ];
    }

    public function includeSigners(Contract $contract)
    {
        return $this->collection($contract->signers, new ContractSignerTransformer());
    }

    public function includeAuditLogs(Contract $contract)
    {
        return $this->collection($contract->auditLogs, new ContractAuditLogTransformer());
    }
}
