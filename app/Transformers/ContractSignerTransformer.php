<?php

namespace App\Transformers;

use App\Models\ContractSigner;
use League\Fractal\TransformerAbstract;

class ContractSignerTransformer extends TransformerAbstract
{
    public function transform(ContractSigner $signer): array
    {
        return [
            'id'                => $signer->id,
            'contract_id'       => $signer->contract_id,
            'client_contact_id' => $signer->client_contact_id,
            'user_id'           => $signer->user_id,
            'name'              => $signer->name,
            'email'             => $signer->email,
            'signing_order'     => $signer->signing_order,
            'is_internal'       => $signer->is_internal,
            'status'            => $signer->status,
            'signed_at'         => $signer->signed_at?->toIso8601String(),
            'declined_at'       => $signer->declined_at?->toIso8601String(),
            'signature_type'    => $signer->signature_type,
            'signed_name'       => $signer->signed_name,
            'signed_title'      => $signer->signed_title,
            'ip_address'        => $signer->ip_address,
            'decline_reason'    => $signer->decline_reason,
            'signing_url'       => $signer->getSigningUrl(),
            'created_at'        => $signer->created_at?->toIso8601String(),
        ];
    }
}
