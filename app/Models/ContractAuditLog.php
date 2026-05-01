<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractAuditLog extends Model
{
    const UPDATED_AT = null; // append-only — no updates ever

    const EVENT_CREATED        = 'created';
    const EVENT_EDITED         = 'edited';
    const EVENT_SENT           = 'sent';
    const EVENT_VIEWED         = 'viewed';
    const EVENT_SIGNED         = 'signed';
    const EVENT_DECLINED       = 'declined';
    const EVENT_DOWNLOADED     = 'downloaded';
    const EVENT_EXPIRED        = 'expired';
    const EVENT_CANCELLED      = 'cancelled';
    const EVENT_REMINDER_SENT  = 'reminder_sent';
    const EVENT_STATUS_CHANGED = 'status_changed';
    const EVENT_PDF_GENERATED  = 'pdf_generated';
    const EVENT_COUNTER_SIGNED = 'counter_signed';
    const EVENT_APPROVED       = 'approved';

    protected $fillable = [
        'contract_id',
        'user_id',
        'contact_id',
        'signer_id',
        'event',
        'old_status',
        'new_status',
        'ip_address',
        'user_agent',
        'actor_type',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(ClientContact::class, 'contact_id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(ContractSigner::class, 'signer_id');
    }

    // Prevent updates — audit logs are immutable
    public function save(array $options = []): bool
    {
        if ($this->exists) {
            return false;
        }
        return parent::save($options);
    }
}
