<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractSigner;
use App\Services\Contract\ContractPdfService;
use App\Services\Contract\ContractService;
use App\Services\Contract\ContractVariableService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractSigningController extends Controller
{
    public function __construct(
        private ContractService         $contractService,
        private ContractVariableService $variableService,
        private ContractPdfService      $pdfService,
    ) {}

    /** Public signing page accessed via the contract's public_token */
    public function show(string $token): View
    {
        $contract = Contract::where('public_token', $token)
            ->with(['client', 'contact', 'company', 'invoice', 'quote', 'signers'])
            ->firstOrFail();

        if ($contract->isExpired()) {
            return view('contracts.expired', compact('contract'));
        }

        if ($contract->status === Contract::STATUS_SIGNED) {
            return view('contracts.signed', compact('contract'));
        }

        if ($contract->status === Contract::STATUS_DECLINED) {
            return view('contracts.declined', compact('contract'));
        }

        $this->contractService->markViewed($contract, request()->ip(), request()->userAgent());

        return view('contracts.sign', [
            'contract'       => $contract,
            'rendered_body'  => $this->variableService->render($contract->contract_body ?? '', $contract),
            'signers'        => $contract->signers,
            'current_signer' => null,
        ]);
    }

    /** Per-signer page accessed via ContractSigner.token */
    public function signerShow(string $signerToken): View
    {
        $signer   = ContractSigner::where('token', $signerToken)->firstOrFail();
        $contract = $signer->contract()->with(['client', 'company', 'signers', 'invoice', 'quote'])->first();

        if ($contract->isExpired()) {
            return view('contracts.expired', compact('contract'));
        }

        if ($signer->isSigned()) {
            return view('contracts.signed', compact('contract'));
        }

        if ($contract->status === Contract::STATUS_DECLINED) {
            return view('contracts.declined', compact('contract'));
        }

        $this->contractService->markViewed($contract, request()->ip(), request()->userAgent());

        return view('contracts.sign', [
            'contract'       => $contract,
            'rendered_body'  => $this->variableService->render($contract->contract_body ?? '', $contract),
            'signers'        => $contract->signers,
            'current_signer' => $signer,
        ]);
    }

    /** Handle signature submission */
    public function sign(Request $request, string $token): RedirectResponse
    {
        $contract = Contract::where('public_token', $token)->firstOrFail();

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
            'email_confirm'  => 'nullable|email',
        ]);

        // Validate signature data is a PNG data URI
        if (!str_starts_with($validated['signature_data'], 'data:image/png;base64,')) {
            return back()->withErrors(['signature_data' => 'Invalid signature format. Please draw your signature again.']);
        }

        $signer = ContractSigner::where('token', $validated['signer_token'])
            ->where('contract_id', $contract->id)
            ->firstOrFail();

        if ($signer->isSigned()) {
            return redirect()->route('contract.signed', $token)
                ->with('info', 'You have already signed this contract.');
        }

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

        return redirect()->route('contract.signed', $token);
    }

    public function signed(string $token): View
    {
        $contract = Contract::where('public_token', $token)
            ->with(['company', 'client', 'signers'])
            ->firstOrFail();

        return view('contracts.signed', compact('contract'));
    }

    public function decline(Request $request, string $token): RedirectResponse
    {
        $contract = Contract::where('public_token', $token)->firstOrFail();

        $validated = $request->validate([
            'signer_token' => 'required|string',
            'reason'       => 'nullable|string|max:1000',
        ]);

        $signer = ContractSigner::where('token', $validated['signer_token'])
            ->where('contract_id', $contract->id)
            ->firstOrFail();

        $this->contractService->decline($contract, $signer, $validated['reason'] ?? '');

        return redirect()->route('contract.declined', $token);
    }

    public function declined(string $token): View
    {
        $contract = Contract::where('public_token', $token)
            ->with(['company'])
            ->firstOrFail();

        return view('contracts.declined', compact('contract'));
    }

    public function download(string $token)
    {
        $contract = Contract::where('public_token', $token)->firstOrFail();
        return $this->pdfService->getDownloadResponse($contract, $contract->isSigned());
    }
}
