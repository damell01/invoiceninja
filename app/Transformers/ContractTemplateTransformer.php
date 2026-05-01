<?php

namespace App\Transformers;

use App\Models\ContractTemplate;
use League\Fractal\TransformerAbstract;

class ContractTemplateTransformer extends TransformerAbstract
{
    public function transform(ContractTemplate $template): array
    {
        return [
            'id'             => $template->id,
            'company_id'     => $template->company_id,
            'user_id'        => $template->user_id,
            'name'           => $template->name,
            'description'    => $template->description,
            'category'       => $template->category,
            'contract_body'  => $template->contract_body,
            'default_clauses' => $template->default_clauses,
            'is_active'      => $template->is_active,
            'is_default'     => $template->is_default,
            'version'        => $template->version,
            'created_at'     => $template->created_at?->toIso8601String(),
            'updated_at'     => $template->updated_at?->toIso8601String(),
            'deleted_at'     => $template->deleted_at?->toIso8601String(),
        ];
    }
}
