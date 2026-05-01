<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Services\Contract\ContractService;
use App\Transformers\ContractTemplateTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractTemplateController extends BaseController
{
    protected $entityType        = ContractTemplate::class;
    protected $entityTransformer = ContractTemplateTransformer::class;

    public function __construct(private ContractService $contractService)
    {
        parent::__construct();
    }

    public function index(): JsonResponse
    {
        $templates = ContractTemplate::where('company_id', auth()->user()->company()->id)
            ->withTrashed()
            ->paginate(request('per_page', 25));

        return $this->listResponse($templates);
    }

    public function show(ContractTemplate $contractTemplate): JsonResponse
    {
        if ($contractTemplate->company_id !== auth()->user()->company()->id) {
            abort(403);
        }
        return $this->itemResponse($contractTemplate);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'category'       => 'nullable|string|max:100',
            'contract_body'  => 'nullable|string',
            'default_clauses' => 'nullable|array',
            'is_active'      => 'boolean',
            'is_default'     => 'boolean',
        ]);

        $validated['company_id'] = auth()->user()->company()->id;
        $validated['user_id']    = auth()->id();

        return $this->itemResponse(ContractTemplate::create($validated));
    }

    public function update(Request $request, ContractTemplate $contractTemplate): JsonResponse
    {
        if ($contractTemplate->company_id !== auth()->user()->company()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'description'    => 'nullable|string',
            'category'       => 'nullable|string|max:100',
            'contract_body'  => 'nullable|string',
            'default_clauses' => 'nullable|array',
            'is_active'      => 'boolean',
            'is_default'     => 'boolean',
        ]);

        $contractTemplate->update($validated);
        return $this->itemResponse($contractTemplate->fresh());
    }

    public function destroy(ContractTemplate $contractTemplate): JsonResponse
    {
        if ($contractTemplate->company_id !== auth()->user()->company()->id) {
            abort(403);
        }
        $contractTemplate->delete();
        return $this->itemResponse($contractTemplate);
    }

    public function clone(ContractTemplate $contractTemplate): JsonResponse
    {
        if ($contractTemplate->company_id !== auth()->user()->company()->id) {
            abort(403);
        }
        $clone             = $contractTemplate->replicate();
        $clone->name       = $contractTemplate->name . ' (Copy)';
        $clone->is_default = false;
        $clone->save();
        return $this->itemResponse($clone);
    }

    public function createContractFromTemplate(Request $request, ContractTemplate $contractTemplate): JsonResponse
    {
        if ($contractTemplate->company_id !== auth()->user()->company()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title'      => 'required|string|max:500',
            'client_id'  => 'nullable|integer',
            'contact_id' => 'nullable|integer',
            'invoice_id' => 'nullable|integer',
            'quote_id'   => 'nullable|integer',
            'expires_at' => 'nullable|date',
        ]);

        $validated['template_id']    = $contractTemplate->id;
        $validated['contract_body']  = $contractTemplate->contract_body;

        $contract = $this->contractService->create($validated, auth()->user()->company(), auth()->user());

        return response()->json(['data' => (new \App\Transformers\ContractTransformer())->transform($contract->fresh())], 201);
    }

    public function cloneContractToTemplate(Contract $contract): JsonResponse
    {
        if ($contract->company_id !== auth()->user()->company()->id) {
            abort(403);
        }

        $template = ContractTemplate::create([
            'company_id'    => auth()->user()->company()->id,
            'user_id'       => auth()->id(),
            'name'          => 'Template from: ' . $contract->title,
            'contract_body' => $contract->contract_body,
            'is_active'     => true,
        ]);

        return $this->itemResponse($template);
    }

    public function bulk(Request $request): JsonResponse
    {
        $action    = $request->input('action');
        $ids       = $request->input('ids', []);
        $companyId = auth()->user()->company()->id;

        ContractTemplate::whereIn('id', $ids)
            ->where('company_id', $companyId)
            ->get()
            ->each(fn ($t) => match ($action) {
                'delete' => $t->delete(),
                default  => null,
            });

        return response()->json(['data' => true]);
    }

    public function categories(): JsonResponse
    {
        return response()->json(['data' => ContractTemplate::categories()]);
    }
}
