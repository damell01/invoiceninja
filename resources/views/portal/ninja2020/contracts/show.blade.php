@extends('portal.ninja2020.layout.app')
@section('meta_title', $contract->title)

@push('head')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
<style>
    .sig-canvas-wrap { border: 2px dashed #d1d5db; border-radius: 8px; background: #f9fafb; position: relative; touch-action: none; }
    .sig-canvas-wrap canvas { display: block; width: 100%; height: 180px; }
    .sig-mode-btn.active { background: #4f46e5; color: #fff; border-color: #4f46e5; }
</style>
@endpush

@section('body')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-4">
        <a href="{{ route('client.contracts.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Back to Contracts</a>
    </div>

    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $contract->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">#{{ $contract->contract_number }}</p>
        </div>
        <div class="flex flex-col items-end gap-2">
            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                @if($contract->status === 'signed') bg-green-100 text-green-700
                @elseif(in_array($contract->status, ['sent','viewed'])) bg-amber-100 text-amber-800
                @else bg-gray-100 text-gray-600 @endif">
                {{ ucfirst(str_replace('_', ' ', $contract->status)) }}
            </span>
            @if($contract->signed_pdf_path || $contract->pdf_path)
            <a href="{{ route('client.contract.download', $contract->public_token) }}" class="text-xs text-indigo-600 hover:underline">Download PDF &darr;</a>
            @endif
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
        <div class="p-6 border-b border-gray-100 bg-gray-50 rounded-t-xl">
            <h2 class="font-semibold text-gray-700">Contract</h2>
        </div>
        <div class="p-6 prose prose-sm max-w-none text-gray-700 leading-relaxed">
            {!! $renderedBody !!}
        </div>
    </div>

    @if($signer && $signer->status === 'pending' && $contract->status !== 'expired' && $contract->status !== 'cancelled')
    <div class="bg-white border border-indigo-200 rounded-xl shadow-sm mb-6">
        <div class="p-5 border-b border-gray-100 bg-indigo-50 rounded-t-xl">
            <h2 class="font-semibold text-indigo-800">Your Signature Required</h2>
        </div>
        <div class="p-6">
            @if(session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>
            @endif
            <form id="portal-sign-form" method="POST" action="{{ route('client.contract.sign', $contract->public_token) }}">
                @csrf
                <input type="hidden" name="signer_token" value="{{ $signer->token }}">
                <input type="hidden" name="signature_data" id="portal-signature-data">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="signed_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300" placeholder="Your legal full name">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title / Role</label>
                    <input type="text" name="signed_title" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300" placeholder="e.g. CEO">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Signature *</label>
                    <div class="flex gap-2 mb-3">
                        <button type="button" class="sig-mode-btn active px-4 py-1.5 text-sm border border-gray-300 rounded-lg" data-mode="draw" onclick="portalSwitchMode('draw')">Draw</button>
                        <button type="button" class="sig-mode-btn px-4 py-1.5 text-sm border border-gray-300 rounded-lg" data-mode="type" onclick="portalSwitchMode('type')">Type</button>
                    </div>
                    <div id="portal-draw-wrap">
                        <div class="sig-canvas-wrap">
                            <canvas id="portal-sig-canvas"></canvas>
                        </div>
                        <button type="button" class="text-xs text-gray-400 hover:text-gray-600 mt-1 underline" onclick="portalClearCanvas()">Clear</button>
                    </div>
                    <div id="portal-type-wrap" style="display:none;">
                        <input type="text" id="portal-typed-sig" class="w-full border border-gray-300 rounded-lg px-3 py-2" style="font-family:'Dancing Script',cursive;font-size:1.5rem;" placeholder="Type your name" oninput="portalRenderTyped(this.value)">
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 bg-green-50 border border-green-200 rounded-lg mb-5">
                    <input type="checkbox" id="portal-consent" name="consent" required class="mt-0.5 w-4 h-4 flex-shrink-0">
                    <label for="portal-consent" class="text-sm text-green-800">
                        By checking this box, I agree this electronic signature is legally binding and I consent to the terms of this contract.
                    </label>
                </div>

                <button type="submit" id="portal-submit-btn" class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition text-sm">
                    Sign Contract
                </button>
            </form>
        </div>
    </div>
    @elseif($signer && $signer->status === 'signed')
    <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-6 text-center">
        <p class="text-green-800 font-medium">&#10003; You signed this contract on {{ $signer->signed_at ? $signer->signed_at->format('M d, Y') : '' }}.</p>
        @if($contract->signed_pdf_path || $contract->pdf_path)
        <a href="{{ route('client.contract.download', $contract->public_token) }}" class="inline-block mt-2 text-sm text-green-700 underline">Download your signed copy</a>
        @endif
    </div>
    @endif

    @if($contract->expires_at)
    <p class="text-xs text-gray-400 text-center">This contract {{ $contract->expires_at->isPast() ? 'expired' : 'expires' }} on {{ $contract->expires_at->format('M d, Y') }}.</p>
    @endif
</div>

@if($signer && $signer->status === 'pending')
<script>
let portalPad;
let portalMode = 'draw';
let portalTypedData = null;

(function() {
    const canvas = document.getElementById('portal-sig-canvas');
    const dpr = window.devicePixelRatio || 1;
    const w = canvas.parentElement.offsetWidth || 600;
    canvas.width = w * dpr;
    canvas.height = 180 * dpr;
    canvas.getContext('2d').scale(dpr, dpr);
    portalPad = new SignaturePad(canvas, { backgroundColor: 'rgb(249,250,251)' });
})();

function portalClearCanvas() { portalPad && portalPad.clear(); }

function portalSwitchMode(mode) {
    portalMode = mode;
    document.querySelectorAll('.sig-mode-btn').forEach(b => b.classList.toggle('active', b.dataset.mode === mode));
    document.getElementById('portal-draw-wrap').style.display = mode === 'draw' ? '' : 'none';
    document.getElementById('portal-type-wrap').style.display = mode === 'type' ? '' : 'none';
}

function portalRenderTyped(name) {
    const c = document.createElement('canvas');
    c.width = 600; c.height = 200;
    const ctx = c.getContext('2d');
    ctx.fillStyle = '#f9fafb'; ctx.fillRect(0, 0, 600, 200);
    ctx.fillStyle = '#1a1a2e'; ctx.font = '56px "Dancing Script",cursive';
    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    ctx.fillText(name, 300, 100);
    portalTypedData = c.toDataURL('image/png');
}

document.getElementById('portal-sign-form').addEventListener('submit', function(e) {
    e.preventDefault();
    let sigData;
    if (portalMode === 'draw') {
        if (!portalPad || portalPad.isEmpty()) { alert('Please provide your drawn signature.'); return; }
        sigData = portalPad.toDataURL('image/png');
    } else {
        const v = document.getElementById('portal-typed-sig').value.trim();
        if (!v) { alert('Please type your name as signature.'); return; }
        portalRenderTyped(v);
        sigData = portalTypedData;
    }
    document.getElementById('portal-signature-data').value = sigData;
    document.getElementById('portal-submit-btn').disabled = true;
    document.getElementById('portal-submit-btn').textContent = 'Submitting...';
    this.submit();
});
</script>
@endif
@endsection
