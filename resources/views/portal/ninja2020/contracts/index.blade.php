@extends('portal.ninja2020.layout.app')
@section('meta_title', ctrans('texts.contracts') ?? 'Contracts')

@section('body')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ ctrans('texts.contracts') ?? 'Contracts' }}</h1>
    </div>

    @if($pending->count() > 0)
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-700 mb-3">
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>
                Awaiting Your Signature ({{ $pending->count() }})
            </span>
        </h2>
        <div class="grid gap-4">
            @foreach($pending as $contract)
            <a href="{{ route('client.contract.show', $contract->public_token) }}" class="block bg-white border border-amber-200 rounded-xl p-5 shadow-sm hover:shadow-md transition group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-gray-800 group-hover:text-indigo-600 transition">{{ $contract->title }}</p>
                        <p class="text-sm text-gray-500 mt-1">#{{ $contract->contract_number }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                            {{ ucfirst(str_replace('_', ' ', $contract->status)) }}
                        </span>
                        @if($contract->expires_at)
                        <p class="text-xs text-gray-400 mt-1">Expires {{ $contract->expires_at->format('M d, Y') }}</p>
                        @endif
                    </div>
                </div>
                <div class="mt-3 text-sm font-medium text-indigo-600">Click to review &amp; sign &rarr;</div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($signed->count() > 0)
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-700 mb-3">
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                Signed Contracts ({{ $signed->count() }})
            </span>
        </h2>
        <div class="grid gap-3">
            @foreach($signed as $contract)
            <a href="{{ route('client.contract.show', $contract->public_token) }}" class="flex items-center justify-between bg-white border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md transition group">
                <div>
                    <p class="font-medium text-gray-800 group-hover:text-indigo-600 transition">{{ $contract->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">#{{ $contract->contract_number }} &middot; Signed {{ $contract->signed_at ? $contract->signed_at->format('M d, Y') : '' }}</p>
                </div>
                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Signed</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($other->count() > 0)
    <div>
        <h2 class="text-lg font-semibold text-gray-700 mb-3">Other Contracts</h2>
        <div class="grid gap-3">
            @foreach($other as $contract)
            <a href="{{ route('client.contract.show', $contract->public_token) }}" class="flex items-center justify-between bg-white border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md transition group">
                <div>
                    <p class="font-medium text-gray-700 group-hover:text-indigo-600 transition">{{ $contract->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">#{{ $contract->contract_number }}</p>
                </div>
                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ ucfirst(str_replace('_',' ',$contract->status)) }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($pending->count() === 0 && $signed->count() === 0 && $other->count() === 0)
    <div class="text-center py-20">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="mt-3 text-gray-500 text-sm">No contracts yet.</p>
    </div>
    @endif
</div>
@endsection
