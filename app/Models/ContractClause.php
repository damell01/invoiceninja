<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractClause extends BaseModel
{
    use SoftDeletes;

    const CATEGORY_PAYMENT_TERMS      = 'payment_terms';
    const CATEGORY_LATE_FEES          = 'late_fees';
    const CATEGORY_CANCELLATION       = 'cancellation';
    const CATEGORY_REFUNDS            = 'refunds';
    const CATEGORY_SCOPE_OF_WORK      = 'scope_of_work';
    const CATEGORY_OWNERSHIP_IP       = 'ownership_ip';
    const CATEGORY_HOSTING_TERMS      = 'hosting_terms';
    const CATEGORY_MAINTENANCE_TERMS  = 'maintenance_terms';
    const CATEGORY_CONFIDENTIALITY    = 'confidentiality';
    const CATEGORY_TERMINATION        = 'termination';
    const CATEGORY_CHANGE_REQUESTS    = 'change_requests';
    const CATEGORY_ACCEPTANCE         = 'acceptance_criteria';

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'category',
        'body',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function categories(): array
    {
        return [
            self::CATEGORY_PAYMENT_TERMS     => 'Payment Terms',
            self::CATEGORY_LATE_FEES         => 'Late Fees',
            self::CATEGORY_CANCELLATION      => 'Cancellation',
            self::CATEGORY_REFUNDS           => 'Refunds',
            self::CATEGORY_SCOPE_OF_WORK     => 'Scope of Work',
            self::CATEGORY_OWNERSHIP_IP      => 'Ownership / IP',
            self::CATEGORY_HOSTING_TERMS     => 'Hosting Terms',
            self::CATEGORY_MAINTENANCE_TERMS => 'Maintenance Terms',
            self::CATEGORY_CONFIDENTIALITY   => 'Confidentiality',
            self::CATEGORY_TERMINATION       => 'Termination',
            self::CATEGORY_CHANGE_REQUESTS   => 'Change Requests',
            self::CATEGORY_ACCEPTANCE        => 'Acceptance Criteria',
        ];
    }

    public function scopeCompanyId($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
