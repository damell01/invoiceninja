<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Contract: {{ $contract->title }}</title>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; color: #1a1a2e; }
        .header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 1rem 2rem; display: flex; align-items: center; gap: 1rem; }
        .header h1 { font-size: 1.25rem; font-weight: 600; color: #111827; }
        .header .badge { font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 9999px; background: #fef3c7; color: #92400e; font-weight: 500; }
        .container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,.06); margin-bottom: 1.5rem; overflow: hidden; }
        .card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f3f4f6; background: #f9fafb; }
        .card-header h2 { font-size: 1rem; font-weight: 600; color: #374151; }
        .card-body { padding: 1.5rem; }
        .contract-body { font-size: 0.9375rem; line-height: 1.75; color: #374151; }
        .contract-body h1,.contract-body h2,.contract-body h3 { margin: 1.5rem 0 0.75rem; color: #111827; }
        .contract-body p { margin-bottom: 1rem; }
        .contract-body ul,.contract-body ol { margin: 0.75rem 0 0.75rem 1.5rem; }
        .contract-body table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        .contract-body th,.contract-body td { border: 1px solid #d1d5db; padding: 0.5rem 0.75rem; text-align: left; }
        .contract-body th { background: #f9fafb; font-weight: 600; }
        .signer-info { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
        .info-item label { font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
        .info-item p { font-size: 0.9375rem; color: #111827; margin-top: 0.25rem; }
        .tabs { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; }
        .tab { padding: 0.5rem 1.25rem; border-radius: 8px; border: 1px solid #d1d5db; background: #fff; cursor: pointer; font-size: 0.875rem; font-weight: 500; color: #374151; transition: all .15s; }
        .tab.active { background: #4f46e5; color: #fff; border-color: #4f46e5; }
        .signature-canvas-wrap { border: 2px dashed #d1d5db; border-radius: 8px; background: #f9fafb; position: relative; touch-action: none; }
        .signature-canvas-wrap canvas { display: block; width: 100%; height: 200px; }
        .canvas-label { position: absolute; bottom: 0.5rem; left: 50%; transform: translateX(-50%); font-size: 0.75rem; color: #9ca3af; pointer-events: none; white-space: nowrap; }
        .btn-clear { margin-top: 0.5rem; font-size: 0.8125rem; color: #6b7280; background: none; border: none; cursor: pointer; text-decoration: underline; }
        .typed-wrap { display: none; }
        .typed-wrap input { width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1.5rem; font-family: 'Dancing Script', cursive, Georgia, serif; color: #111827; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.375rem; }
        .form-group input[type=text] { width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.9375rem; color: #111827; }
        .form-group input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.15); }
        .consent-wrap { display: flex; gap: 0.75rem; align-items: flex-start; padding: 1rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; margin-bottom: 1.5rem; }
        .consent-wrap input[type=checkbox] { margin-top: 0.1875rem; flex-shrink: 0; width: 1rem; height: 1rem; }
        .consent-wrap label { font-size: 0.875rem; color: #166534; line-height: 1.5; }
        .btn-sign { width: 100%; padding: 0.875rem; background: #4f46e5; color: #fff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background .15s; }
        .btn-sign:hover { background: #4338ca; }
        .btn-sign:disabled { background: #9ca3af; cursor: not-allowed; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.875rem 1rem; color: #991b1b; font-size: 0.875rem; margin-bottom: 1rem; }
        .meta { font-size: 0.8125rem; color: #6b7280; text-align: center; margin-top: 1rem; }
        @media (max-width: 600px) { .signer-info { grid-template-columns: 1fr; } }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
</head>
<body>
<div class="header">
    <h1>{{ $contract->title }}</h1>
    <span class="badge">Awaiting Your Signature</span>
</div>
<div class="container">
    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header"><h2>Contract Details</h2></div>
        <div class="card-body">
            <div class="signer-info">
                <div class="info-item"><label>Contract #</label><p>{{ $contract->contract_number }}</p></div>
                <div class="info-item"><label>Prepared For</label><p>{{ $signer->name }}</p></div>
                @if($contract->client)
                    <div class="info-item"><label>Company</label><p>{{ $contract->client->name }}</p></div>
                @endif
                @if($contract->expires_at)
                    <div class="info-item"><label>Expires</label><p>{{ $contract->expires_at->format('M d, Y') }}</p></div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Contract</h2></div>
        <div class="card-body">
            <div class="contract-body">{!! $renderedBody !!}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Sign Document</h2></div>
        <div class="card-body">
            <form id="sign-form" method="POST" action="{{ route('contract.sign.submit', $contract->public_token) }}">
                @csrf
                <input type="hidden" name="signer_token" value="{{ $signer->token }}">
                <input type="hidden" name="signature_data" id="signature_data">

                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="signed_name" id="signed_name" placeholder="Your legal full name" required autocomplete="name">
                </div>

                <div class="form-group">
                    <label>Title / Role (optional)</label>
                    <input type="text" name="signed_title" id="signed_title" placeholder="e.g. CEO, Authorized Representative">
                </div>

                <div class="form-group">
                    <label>Signature *</label>
                    <div class="tabs">
                        <button type="button" class="tab active" data-mode="draw" onclick="switchMode('draw')">Draw</button>
                        <button type="button" class="tab" data-mode="type" onclick="switchMode('type')">Type</button>
                    </div>
                    <div id="draw-wrap">
                        <div class="signature-canvas-wrap">
                            <canvas id="sig-canvas"></canvas>
                            <span class="canvas-label">Sign inside the box</span>
                        </div>
                        <button type="button" class="btn-clear" onclick="clearCanvas()">Clear signature</button>
                    </div>
                    <div class="typed-wrap" id="type-wrap">
                        <input type="text" id="typed-sig" placeholder="Type your name as signature" oninput="renderTypedSig(this.value)">
                    </div>
                </div>

                <div class="consent-wrap">
                    <input type="checkbox" id="consent" name="consent" required>
                    <label for="consent">
                        By checking this box and clicking "Sign Contract", I agree that this electronic signature is the legal equivalent of my manual signature on this document. I consent to be legally bound by this agreement.
                    </label>
                </div>

                <button type="submit" class="btn-sign" id="submit-btn">Sign Contract</button>
            </form>
        </div>
    </div>

    <p class="meta">This is a legally binding electronic document. Your IP address and timestamp will be recorded for audit purposes.</p>
</div>

<script>
let signaturePad;
let currentMode = 'draw';

(function initPad() {
    const canvas = document.getElementById('sig-canvas');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.parentElement.getBoundingClientRect();
    canvas.width = (rect.width || 600) * dpr;
    canvas.height = 200 * dpr;
    const ctx = canvas.getContext('2d');
    ctx.scale(dpr, dpr);
    signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(249,250,251)' });
})();

function clearCanvas() {
    if (signaturePad) signaturePad.clear();
}

function switchMode(mode) {
    currentMode = mode;
    document.querySelectorAll('.tab').forEach(t => t.classList.toggle('active', t.dataset.mode === mode));
    document.getElementById('draw-wrap').style.display = mode === 'draw' ? '' : 'none';
    document.getElementById('type-wrap').style.display = mode === 'type' ? '' : 'none';
}

function renderTypedSig(name) {
    const canvas = document.createElement('canvas');
    canvas.width = 600; canvas.height = 200;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#f9fafb';
    ctx.fillRect(0, 0, 600, 200);
    ctx.fillStyle = '#1a1a2e';
    ctx.font = '56px "Dancing Script", cursive';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(name, 300, 100);
    window.__typedSigData = canvas.toDataURL('image/png');
}

document.getElementById('sign-form').addEventListener('submit', function(e) {
    e.preventDefault();
    let sigData = '';
    if (currentMode === 'draw') {
        if (!signaturePad || signaturePad.isEmpty()) {
            alert('Please provide your signature before submitting.');
            return;
        }
        sigData = signaturePad.toDataURL('image/png');
    } else {
        const typedVal = document.getElementById('typed-sig').value.trim();
        if (!typedVal) { alert('Please type your name as your signature.'); return; }
        renderTypedSig(typedVal);
        sigData = window.__typedSigData;
    }
    document.getElementById('signature_data').value = sigData;
    document.getElementById('submit-btn').disabled = true;
    document.getElementById('submit-btn').textContent = 'Submitting...';
    this.submit();
});
</script>
</body>
</html>
