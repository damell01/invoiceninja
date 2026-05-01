<?php

namespace App\Transformers;

use App\Models\ContractAuditLog;
use League\Fractal\TransformerAbstract;

class ContractAuditLogTransformer extends TransformerAbstract
{
    public function transform(ContractAuditLog $log): array
    {
        return [
            'id'          => $log->id,
            'contract_id' => $log->contract_id,
            'user_id'     => $log->user_id,
            'contact_id'  => $log->contact_id,
            'signer_id'   => $log->signer_id,
            'event'       => $log->event,
            'old_status'  => $log->old_status,
            'new_status'  => $log->new_status,
            'ip_address'  => $log->ip_address,
            'actor_type'  => $log->actor_type,
            'notes'       => $log->notes,
            'metadata'    => $log->metadata,
            'created_at'  => $log->created_at?->toIso8601String(),
        ];
    }
}
