<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\Contract\ContractPdfService;
use App\Services\Contract\ContractService;
use App\Services\Contract\ContractVariableService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function __construct(
        private ContractVariableService $variableService,
        private ContractService         $contractService,
        private ContractPdfService      $pdfService,
    ) {}

    public function index(): View
    {
        $contact = auth()->user();
        $client  = $contact->client;

        $pending = Contract::where('client_id', $client->id)
            ->whereIn('status', [Contract::STATUS_SENT, Contract::STATUS_VIEWED])
            ->orderByDesc('created_at')
            ->get();

        $signed = Contract::where('client_id', $client->id)
            ->where('status', Contract::STATUS_SIGNED)
            ->orderByDesc('signed_at')
            ->get();

        $other = Contract::where('client_id', $client->id)
            ->whereIn('status', [Contract::STATUS_DECLINED, Contract::STATUS_EXPIRED, Contract::STATUS_CANCELLED])
            ->orderByDesc('created_at')
            ->get();

        return view('portal.ninja2020.contracts.index', compact('pending', 'signed', 'other', 'client'));
    }

    public function show(string $publicToken): View
    {
        $contact  = auth()->user();
        $client   = $contact->client;

        $contract = Contract::where('public_token', $publicToken)
            ->where('client_id', $client->id)
            ->with(['signers', 'company', 'client', 'invoice', 'quote', 'auditLogs'])
            ->firstOrFail();

        if (in_array($contract->status, [Contract::STATUS_SENT, Contract::STATUS_VIEWED])) {
            $this->contractService->markViewed($contract, request()->ip(), request()->userAgent());
        }

        $signerRecord = $contract->signers()
            ->where('client_contact_id', $contact->id)
            ->first();

        return view('portal.ninja2020.contracts.show', [
            'contract'       => $contract,
            'rendered_body'  => $this->variableService->render($contract->contract_body ?? '', $contract),
            'signer'         => $signerRecord,
            'client'         => $client,
        ]);
    }

    public function sign(Request $request, string $publicToken)
    {
        $contact  = auth()->user();
        $contract = Contract::where('public_token', $publicToken)
            ->where('client_id', $contact->client->id)
            ->firstOrFail();

        if ($contract->isExpired()) {
            return back()->withErrors(['error' => 'This contract has expired.']);
        }

        $validated = $request->validate([
            'signer_token'   => 'required|string',
            'signed_name'    => 'required|string|max:255',
            'signed_title'   => 'nullable|string|max:255',
            'signature_data' => 'required|string',
            'signature_type' => 'nullable|in:drawn,typed',
            'agreed'         => 'accepted',
        ]);

        if (!str_starts_with($validated['signature_data'], 'data:image/png;base64,')) {
            return back()->withErrors(['signature_data' => 'Invalid signature format.']);
        }

        $signer = \App\Models\ContractSigner::where('token', $validated['signer_token'])
            ->where('contract_id', $contract->id)
            ->firstOrFail();

        try {
            $this->contractService->sign($contract, $signer, [
                'signature_data' => $validated['signature_data'],
                'signature_type' => $validated['signature_type'] ?? 'drawn',
                'signed_name'    => $validated['signed_name'],
                'signed_title'   => $validated['signed_title'] ?? null,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('client.contracts.show', $publicToken)
            ->with('success', 'Contract signed successfully!');
    }

    public function download(string $publicToken)
    {
        $contact  = auth()->user();
        $contract = Contract::where('public_token', $publicToken)
            ->where('client_id', $contact->client->id)
            ->firstOrFail();

        return $this->pdfService->getDownloadResponse($contract, $contract->isSigned());
    }
}
