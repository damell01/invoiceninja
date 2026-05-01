<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends BaseModel
{
    use SoftDeletes, HasFactory;

    const STATUS_DRAFT             = 'draft';
    const STATUS_INTERNAL_REVIEW   = 'internal_review';
    const STATUS_APPROVED          = 'approved';
    const STATUS_SENT              = 'sent';
    const STATUS_VIEWED            = 'viewed';
    const STATUS_SIGNED            = 'signed';
    const STATUS_DECLINED          = 'declined';
    const STATUS_EXPIRED           = 'expired';
    const STATUS_CANCELLED         = 'cancelled';

    protected $fillable = [
        'company_id',
        'client_id',
        'contact_id',
        'invoice_id',
        'quote_id',
        'project_id',
        'user_id',
        'assigned_user_id',
        'template_id',
        'parent_id',
        'contract_number',
        'title',
        'status',
        'contract_body',
        'public_token',
        'expires_at',
        'sent_at',
        'viewed_at',
        'signed_at',
        'declined_at',
        'pdf_path',
        'signed_pdf_path',
        'contract_hash',
        'notes',
        'footer',
        'requires_counter_signature',
        'counter_signed_at',
        'counter_signed_by',
        'is_locked',
        'version',
    ];

    protected $casts = [
        'expires_at'                  => 'datetime',
        'sent_at'                     => 'datetime',
        'viewed_at'                   => 'datetime',
        'signed_at'                   => 'datetime',
        'declined_at'                 => 'datetime',
        'counter_signed_at'           => 'datetime',
        'requires_counter_signature'  => 'boolean',
        'is_locked'                   => 'boolean',
        'version'                     => 'integer',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(ClientContact::class, 'contact_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    public function counterSignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counter_signed_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'parent_id');
    }

    public function signers(): HasMany
    {
        return $this->hasMany(ContractSigner::class)->orderBy('signing_order');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ContractAuditLog::class)->latest('created_at');
    }

    // ── Helpers ───────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at
            && $this->expires_at->isPast()
            && !in_array($this->status, [self::STATUS_SIGNED, self::STATUS_DECLINED]);
    }

    public function isSigned(): bool
    {
        return $this->status === self::STATUS_SIGNED;
    }

    public function isEditable(): bool
    {
        return !$this->is_locked && !$this->isSigned();
    }

    public function allSignersSigned(): bool
    {
        return $this->signers()->where('is_internal', false)->where('status', '!=', 'signed')->doesntExist();
    }

    public function getPublicSigningUrl(): string
    {
        return route('contract.sign', ['token' => $this->public_token]);
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeCompanyId($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
