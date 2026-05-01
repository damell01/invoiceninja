<?php

namespace App\Mail\Contract;

use App\Models\Contract;
use App\Models\ContractSigner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Contract       $contract,
        public readonly ContractSigner $signer,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Reminder: Please sign your contract — ' . $this->contract->title);
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.contract.reminder',
            with: [
                'contract'   => $this->contract,
                'signer'     => $this->signer,
                'signingUrl' => $this->signer->getSigningUrl(),
                'company'    => $this->contract->company,
            ],
        );
    }
}
