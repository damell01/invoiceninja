<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\ContractClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractClauseController extends BaseController
{
    public function index(): JsonResponse
    {
        $clauses = ContractClause::where('company_id', auth()->user()->company()->id)
            ->withTrashed()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $clauses]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'category'  => 'nullable|string|max:100',
            'body'      => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = auth()->user()->company()->id;
        $validated['user_id']    = auth()->id();

        return response()->json(['data' => ContractClause::create($validated)], 201);
    }

    public function update(Request $request, ContractClause $contractClause): JsonResponse
    {
        if ($contractClause->company_id !== auth()->user()->company()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name'      => 'sometimes|required|string|max:255',
            'category'  => 'nullable|string|max:100',
            'body'      => 'sometimes|required|string',
            'is_active' => 'boolean',
        ]);

        $contractClause->update($validated);
        return response()->json(['data' => $contractClause->fresh()]);
    }

    public function destroy(ContractClause $contractClause): JsonResponse
    {
        if ($contractClause->company_id !== auth()->user()->company()->id) {
            abort(403);
        }
        $contractClause->delete();
        return response()->json(['data' => true]);
    }

    public function categories(): JsonResponse
    {
        return response()->json(['data' => ContractClause::categories()]);
    }
}
