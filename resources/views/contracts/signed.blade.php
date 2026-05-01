<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Signed</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 24px rgba(0,0,0,.08); max-width: 520px; width: 100%; margin: 2rem; padding: 3rem 2.5rem; text-align: center; }
        .icon { width: 72px; height: 72px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        .icon svg { width: 36px; height: 36px; color: #059669; }
        h1 { font-size: 1.75rem; font-weight: 700; color: #111827; margin-bottom: 0.75rem; }
        p { font-size: 0.9375rem; color: #6b7280; line-height: 1.6; margin-bottom: 1.5rem; }
        .details { background: #f9fafb; border-radius: 10px; padding: 1.25rem; text-align: left; margin-bottom: 2rem; }
        .details dl { display: grid; grid-template-columns: auto 1fr; gap: 0.5rem 1.5rem; }
        .details dt { font-size: 0.8125rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; }
        .details dd { font-size: 0.875rem; color: #111827; }
        .btn { display: inline-block; padding: 0.75rem 2rem; background: #4f46e5; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9375rem; transition: background .15s; }
        .btn:hover { background: #4338ca; }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
    </div>
    <h1>Contract Signed!</h1>
    <p>You have successfully signed <strong>{{ $contract->title }}</strong>. A copy of the signed document will be emailed to you.</p>
    <div class="details">
        <dl>
            <dt>Contract #</dt><dd>{{ $contract->contract_number }}</dd>
            <dt>Signed By</dt><dd>{{ $signer->name }}</dd>
            <dt>Date</dt><dd>{{ $signer->signed_at ? $signer->signed_at->format('M d, Y H:i T') : now()->format('M d, Y H:i T') }}</dd>
        </dl>
    </div>
    @if($downloadUrl)
        <a href="{{ $downloadUrl }}" class="btn">Download Signed Copy</a>
    @endif
</div>
</body>
</html>
