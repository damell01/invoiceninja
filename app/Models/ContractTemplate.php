<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractTemplate extends BaseModel
{
    use SoftDeletes;

    const CATEGORY_WEBSITE_PROJECT      = 'website_project';
    const CATEGORY_MONTHLY_MAINTENANCE  = 'monthly_maintenance';
    const CATEGORY_SAAS_SUBSCRIPTION    = 'saas_subscription';
    const CATEGORY_CONSULTING_AGREEMENT = 'consulting_agreement';
    const CATEGORY_SERVICE_AGREEMENT    = 'service_agreement';
    const CATEGORY_RETAINER_AGREEMENT   = 'retainer_agreement';
    const CATEGORY_PROPOSAL_TO_CONTRACT = 'proposal_to_contract';

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'description',
        'category',
        'contract_body',
        'default_clauses',
        'is_active',
        'is_default',
        'version',
    ];

    protected $casts = [
        'default_clauses' => 'array',
        'is_active'       => 'boolean',
        'is_default'      => 'boolean',
        'version'         => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'template_id');
    }

    public static function categories(): array
    {
        return [
            self::CATEGORY_WEBSITE_PROJECT      => 'Website Project',
            self::CATEGORY_MONTHLY_MAINTENANCE  => 'Monthly Maintenance',
            self::CATEGORY_SAAS_SUBSCRIPTION    => 'SaaS Subscription',
            self::CATEGORY_CONSULTING_AGREEMENT => 'Consulting Agreement',
            self::CATEGORY_SERVICE_AGREEMENT    => 'Service Agreement',
            self::CATEGORY_RETAINER_AGREEMENT   => 'Retainer Agreement',
            self::CATEGORY_PROPOSAL_TO_CONTRACT => 'Proposal to Contract',
        ];
    }

    public function scopeCompanyId($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
