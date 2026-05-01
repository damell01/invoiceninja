<?php

namespace App\Services\Contract;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ContractPdfService
{
    public function __construct(private ContractVariableService $variableService) {}

    public function generate(Contract $contract, bool $isSigned = false): string
    {
        $contract->loadMissing(['company', 'client', 'contact', 'invoice', 'quote', 'project', 'user', 'signers', 'auditLogs']);

        $renderedBody = $this->variableService->render($contract->contract_body ?? '', $contract);

        $data = [
            'contract'     => $contract,
            'rendered_body' => $renderedBody,
            'is_signed'    => $isSigned,
            'signers'      => $isSigned ? $contract->signers()->where('status', 'signed')->get() : collect(),
            'audit_logs'   => $isSigned ? $contract->auditLogs()->orderBy('created_at')->get() : collect(),
            'company'      => $contract->company,
            'client'       => $contract->client,
            'generated_at' => now(),
        ];

        $pdf = Pdf::loadView('contracts.pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'defaultFont'          => 'DejaVu Sans',
        ]);

        $dir      = $isSigned ? 'signed' : 'unsigned';
        $filename = 'contracts/' . $contract->company_id . '/' . $dir . '/' . $contract->public_token . '.pdf';

        Storage::disk('local')->put($filename, $pdf->output());

        return $filename;
    }

    public function getDownloadResponse(Contract $contract, bool $signed = false): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $path = $signed ? $contract->signed_pdf_path : $contract->pdf_path;

        if (!$path || !Storage::disk('local')->exists($path)) {
            $path = $this->generate($contract, $signed);
        }

        $filename = 'contract-' . ($contract->contract_number ?? $contract->id) . ($signed ? '-signed' : '') . '.pdf';

        return response()->streamDownload(
            fn () => print(Storage::disk('local')->get($path)),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }
}
