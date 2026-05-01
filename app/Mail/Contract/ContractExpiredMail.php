<?php

namespace App\Mail\Contract;

use App\Models\Contract;
use App\Models\ContractSigner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Contract        $contract,
        public readonly ?ContractSigner $signer,
        public readonly bool            $isWarning = true,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isWarning
            ? 'Your contract is expiring soon: ' . $this->contract->title
            : 'Contract expired: ' . $this->contract->title;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.contract.expired',
            with: [
                'contract'   => $this->contract,
                'signer'     => $this->signer,
                'isWarning'  => $this->isWarning,
                'signingUrl' => $this->signer?->getSigningUrl(),
                'company'    => $this->contract->company,
            ],
        );
    }
}
