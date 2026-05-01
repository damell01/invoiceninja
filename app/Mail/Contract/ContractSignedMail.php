<?php

namespace App\Mail\Contract;

use App\Models\Contract;
use App\Models\ContractSigner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractSignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Contract        $contract,
        public readonly ?ContractSigner $signer,
        public readonly bool            $isAdminCopy = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Contract Signed: ' . $this->contract->title);
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.contract.signed',
            with: [
                'contract'    => $this->contract,
                'signer'      => $this->signer,
                'isAdminCopy' => $this->isAdminCopy,
                'downloadUrl' => route('contract.download', $this->contract->public_token),
                'company'     => $this->contract->company,
            ],
        );
    }
}
