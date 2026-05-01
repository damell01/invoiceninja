<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractSigner extends Model
{
    const STATUS_PENDING  = 'pending';
    const STATUS_VIEWED   = 'viewed';
    const STATUS_SIGNED   = 'signed';
    const STATUS_DECLINED = 'declined';

    protected $fillable = [
        'contract_id',
        'client_contact_id',
        'user_id',
        'name',
        'email',
        'signing_order',
        'is_internal',
        'status',
        'token',
        'signed_at',
        'declined_at',
        'signature_data',
        'signature_type',
        'signed_name',
        'signed_title',
        'ip_address',
        'user_agent',
        'decline_reason',
    ];

    protected $casts = [
        'signed_at'    => 'datetime',
        'declined_at'  => 'datetime',
        'is_internal'  => 'boolean',
        'signing_order' => 'integer',
    ];

    protected $hidden = ['signature_data', 'token'];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function clientContact(): BelongsTo
    {
        return $this->belongsTo(ClientContact::class, 'client_contact_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSigned(): bool
    {
        return $this->status === self::STATUS_SIGNED;
    }

    public function isDeclined(): bool
    {
        return $this->status === self::STATUS_DECLINED;
    }

    public function getSigningUrl(): string
    {
        return route('contract.signer.sign', ['signerToken' => $this->token]);
    }
}
