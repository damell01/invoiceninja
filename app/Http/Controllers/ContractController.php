<?php

namespace App\Http\Controllers;

use App\Filters\ContractFilters;
use App\Http\Controllers\BaseController;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Quote;
use App\Services\Contract\ContractPdfService;
use App\Services\Contract\ContractService;
use App\Services\Contract\ContractVariableService;
use App\Transformers\ContractTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends BaseController
{
    protected $entityType        = Contract::class;
    protected $entityTransformer = ContractTransformer::class;

    public function __construct(
        private ContractService         $contractService,
        private ContractVariableService $variableService,
        private ContractPdfService      $pdfService,
    ) {
        parent::__construct();
    }

    public function index(ContractFilters $filters): JsonResponse
    {
        $contracts = Contract::filter($filters)
            ->where('company_id', auth()->user()->company()->id)
            ->withTrashed()
            ->paginate(request('per_page', 25));

        return $this->listResponse($contracts);
    }

    public function show(Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);
        return $this->itemResponse($contract);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Contract::class);

        $validated = $request->validate([
            'title'                      => 'required|string|max:500',
            'client_id'                  => 'nullable|integer',
            'contact_id'                 => 'nullable|integer',
            'invoice_id'                 => 'nullable|integer',
            'quote_id'                   => 'nullable|integer',
            'project_id'                 => 'nullable|integer',
            'template_id'                => 'nullable|integer',
            'contract_body'              => 'nullable|string',
            'expires_at'                 => 'nullable|date',
            'notes'                      => 'nullable|string|max:5000',
            'footer'                     => 'nullable|string|max:2000',
            'requires_counter_signature' => 'boolean',
            'assigned_user_id'           => 'nullable|integer',
        ]);

        $contract = $this->contractService->create(
            $validated,
            auth()->user()->company(),
            auth()->user()
        );

        return $this->itemResponse($contract->fresh(['signers']));
    }

    public function update(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('update', $contract);

        $validated = $request->validate([
            'title'                      => 'sometimes|required|string|max:500',
            'client_id'                  => 'nullable|integer',
            'contact_id'                 => 'nullable|integer',
            'invoice_id'                 => 'nullable|integer',
            'quote_id'                   => 'nullable|integer',
            'project_id'                 => 'nullable|integer',
            'template_id'                => 'nullable|integer',
            'contract_body'              => 'nullable|string',
            'expires_at'                 => 'nullable|date',
            'notes'                      => 'nullable|string|max:5000',
            'footer'                     => 'nullable|string|max:2000',
            'requires_counter_signature' => 'boolean',
            'assigned_user_id'           => 'nullable|integer',
            'status'                     => 'sometimes|string|in:draft,internal_review,approved,cancelled',
        ]);

        $contract = $this->contractService->update($contract, $validated);
        return $this->itemResponse($contract);
    }

    public function destroy(Contract $contract): JsonResponse
    {
        $this->authorize('delete', $contract);
        $contract->delete();
        return $this->itemResponse($contract);
    }

    // ── Actions ────────────────────────────────────────────────

    public function send(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('send', $contract);

        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:5000',
        ]);

        $contract = $this->contractService->send($contract, $validated);
        return $this->itemResponse($contract);
    }

    public function approve(Contract $contract): JsonResponse
    {
        $this->authorize('update', $contract);
        return $this->itemResponse($this->contractService->approve($contract, auth()->user()));
    }

    public function cancel(Contract $contract): JsonResponse
    {
        $this->authorize('update', $contract);
        return $this->itemResponse($this->contractService->cancel($contract));
    }

    public function counterSign(Contract $contract): JsonResponse
    {
        $this->authorize('update', $contract);
        return $this->itemResponse($this->contractService->counterSign($contract, auth()->user()));
    }

    public function newVersion(Contract $contract): JsonResponse
    {
        $this->authorize('create', Contract::class);
        return $this->itemResponse($this->contractService->createNewVersion($contract));
    }

    // ── Signers ───────────────────────────────────────────────

    public function addSigner(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('update', $contract);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|max:255',
            'client_contact_id' => 'nullable|integer',
            'signing_order'     => 'integer|min:1',
            'is_internal'       => 'boolean',
            'user_id'           => 'nullable|integer',
        ]);

        $signer = $this->contractService->addSigner($contract, $validated);
        return response()->json(['data' => $signer], 201);
    }

    public function removeSigner(Contract $contract, \App\Models\ContractSigner $signer): JsonResponse
    {
        $this->authorize('update', $contract);

        if ($signer->isSigned()) {
            return response()->json(['message' => 'Cannot remove a signer who has already signed.'], 422);
        }

        $signer->delete();
        return response()->json(['data' => true]);
    }

    // ── PDF / Download ─────────────────────────────────────────

    public function generatePdf(Contract $contract)
    {
        $this->authorize('view', $contract);
        $this->contractService->generatePdf($contract);
        return $this->itemResponse($contract->fresh());
    }

    public function downloadPdf(Contract $contract)
    {
        $this->authorize('view', $contract);
        return $this->pdfService->getDownloadResponse($contract, false);
    }

    public function downloadSignedPdf(Contract $contract)
    {
        $this->authorize('view', $contract);
        return $this->pdfService->getDownloadResponse($contract, true);
    }

    // ── Audit / Preview / Variables ─────────────────────────────

    public function auditLog(Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);
        return response()->json(['data' => $contract->auditLogs()->orderBy('created_at')->get()]);
    }

    public function variables(): JsonResponse
    {
        return response()->json(['data' => $this->variableService->getAvailableVariables()]);
    }

    public function preview(Request $request, Contract $contract): JsonResponse
    {
        $this->authorize('view', $contract);
        $body     = $request->input('contract_body', $contract->contract_body ?? '');
        $rendered = $this->variableService->render($body, $contract);
        return response()->json(['rendered' => $rendered]);
    }

    // ── Conversion helpers ───────────────────────────────────────

    public function fromQuote(Request $request, Quote $quote): JsonResponse
    {
        $this->authorize('create', Contract::class);

        $contract = $this->contractService->create([
            'title'          => 'Contract for Quote #' . $quote->number,
            'client_id'      => $quote->client_id,
            'contact_id'     => $quote->invitations->first()?->client_contact_id,
            'quote_id'       => $quote->id,
            'contract_body'  => $request->input('contract_body', ''),
        ], auth()->user()->company(), auth()->user());

        return $this->itemResponse($contract->fresh());
    }

    public function fromInvoice(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('create', Contract::class);

        $contract = $this->contractService->create([
            'title'         => 'Contract for Invoice #' . $invoice->number,
            'client_id'     => $invoice->client_id,
            'contact_id'    => $invoice->invitations->first()?->client_contact_id,
            'invoice_id'    => $invoice->id,
            'contract_body' => $request->input('contract_body', ''),
        ], auth()->user()->company(), auth()->user());

        return $this->itemResponse($contract->fresh());
    }

    // ── Bulk ────────────────────────────────────────────────────

    public function bulk(Request $request): JsonResponse
    {
        $action    = $request->input('action');
        $ids       = $request->input('ids', []);
        $companyId = auth()->user()->company()->id;

        Contract::whereIn('id', $ids)
            ->where('company_id', $companyId)
            ->get()
            ->each(function (Contract $contract) use ($action) {
                match ($action) {
                    'delete'  => $contract->delete(),
                    'cancel'  => $this->contractService->cancel($contract),
                    'send'    => $this->contractService->send($contract),
                    'approve' => $this->contractService->approve($contract, auth()->user()),
                    default   => null,
                };
            });

        return response()->json(['data' => true]);
    }
}
