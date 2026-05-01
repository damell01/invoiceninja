<?php

namespace App\Mail\Contract;

use App\Models\Contract;
use App\Models\ContractSigner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Contract       $contract,
        public readonly ContractSigner $signer,
        public string                  $emailSubject = '',
        public string                  $emailMessage = '',
    ) {
        if (empty($this->emailSubject)) {
            $this->emailSubject = 'Contract Ready for Signature: ' . $contract->title;
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->emailSubject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.contract.sent',
            with: [
                'contract'    => $this->contract,
                'signer'      => $this->signer,
                'signingUrl'  => $this->signer->getSigningUrl(),
                'emailMessage' => $this->emailMessage,
                'company'     => $this->contract->company,
            ],
        );
    }
}
