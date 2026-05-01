<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1a1a1a; margin: 0; padding: 0; }
    .page { padding: 40px 50px; }
    .header { border-bottom: 2px solid #4f46e5; padding-bottom: 18px; margin-bottom: 24px; }
    .header-grid { display: table; width: 100%; }
    .header-left { display: table-cell; vertical-align: top; }
    .header-right { display: table-cell; vertical-align: top; text-align: right; }
    .company-name { font-size: 16pt; font-weight: bold; color: #4f46e5; }
    .doc-title { font-size: 22pt; font-weight: bold; color: #111827; margin: 8px 0 4px; }
    .doc-meta { font-size: 9pt; color: #6b7280; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; }
    .badge-signed { background: #d1fae5; color: #065f46; }
    .badge-draft { background: #f3f4f6; color: #374151; }
    .parties { margin-bottom: 24px; }
    .parties-grid { display: table; width: 100%; border: 1px solid #e5e7eb; border-radius: 6px; }
    .party { display: table-cell; padding: 14px 16px; vertical-align: top; }
    .party + .party { border-left: 1px solid #e5e7eb; }
    .party-label { font-size: 8pt; font-weight: bold; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
    .party-name { font-size: 11pt; font-weight: bold; color: #111827; }
    .party-detail { font-size: 9pt; color: #374151; margin-top: 2px; }
    .section-label { font-size: 8pt; font-weight: bold; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
    .contract-body { font-size: 10pt; line-height: 1.7; color: #374151; }
    .contract-body h1 { font-size: 14pt; font-weight: bold; color: #111827; margin: 18px 0 8px; }
    .contract-body h2 { font-size: 12pt; font-weight: bold; color: #111827; margin: 14px 0 6px; }
    .contract-body h3 { font-size: 10pt; font-weight: bold; color: #374151; margin: 10px 0 4px; }
    .contract-body p { margin: 0 0 10px; }
    .contract-body ul,.contract-body ol { margin: 6px 0 10px 18px; }
    .contract-body table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 9pt; }
    .contract-body th,.contract-body td { border: 1px solid #d1d5db; padding: 6px 10px; text-align: left; }
    .contract-body th { background: #f9fafb; font-weight: bold; }
    .divider { border: none; border-top: 1px solid #e5e7eb; margin: 28px 0; }
    .signature-section { margin-top: 32px; }
    .sig-grid { display: table; width: 100%; }
    .sig-block { display: table-cell; padding-right: 40px; vertical-align: top; }
    .sig-image-wrap { border: 1px solid #e5e7eb; background: #f9fafb; height: 80px; border-radius: 6px; margin-bottom: 6px; text-align: center; padding-top: 10px; }
    .sig-image-wrap img { max-height: 60px; max-width: 100%; }
    .sig-line { border-top: 1px solid #374151; margin-bottom: 4px; }
    .sig-name { font-size: 10pt; font-weight: bold; color: #111827; }
    .sig-detail { font-size: 8pt; color: #6b7280; margin-top: 2px; }
    .certificate-page { page-break-before: always; padding: 40px 50px; }
    .cert-header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 28px; }
    .cert-title { font-size: 16pt; font-weight: bold; color: #111827; margin-bottom: 6px; }
    .cert-subtitle { font-size: 10pt; color: #6b7280; }
    .cert-hash { font-family: monospace; font-size: 8pt; background: #f3f4f6; padding: 8px 12px; border-radius: 4px; word-break: break-all; color: #374151; margin: 16px 0; }
    .audit-table { width: 100%; border-collapse: collapse; font-size: 8pt; }
    .audit-table th { background: #4f46e5; color: #fff; padding: 7px 10px; text-align: left; }
    .audit-table td { border-bottom: 1px solid #f3f4f6; padding: 7px 10px; color: #374151; vertical-align: top; }
    .audit-table tr:nth-child(even) td { background: #f9fafb; }
    .footer { margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 12px; font-size: 8pt; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-grid">
            <div class="header-left">
                <div class="company-name">{{ $contract->company->settings->name ?? $contract->company->name ?? 'Company' }}</div>
                <div class="doc-title">{{ $contract->title }}</div>
                <div class="doc-meta">Contract #{{ $contract->contract_number }} &nbsp;·&nbsp; Version {{ $contract->version }}</div>
            </div>
            <div class="header-right">
                <span class="badge {{ $isSigned ? 'badge-signed' : 'badge-draft' }}">{{ $isSigned ? 'Signed' : strtoupper(str_replace('_',' ',$contract->status)) }}</span>
                <div class="doc-meta" style="margin-top:8px">
                    Created: {{ $contract->created_at->format('M d, Y') }}<br>
                    @if($contract->expires_at) Expires: {{ $contract->expires_at->format('M d, Y') }} @endif
                    @if($isSigned && $contract->signed_at) Signed: {{ $contract->signed_at->format('M d, Y') }} @endif
                </div>
            </div>
        </div>
    </div>

    <div class="parties">
        <div class="parties-grid">
            <div class="party">
                <div class="party-label">Service Provider</div>
                <div class="party-name">{{ $contract->company->settings->name ?? $contract->company->name ?? 'Company' }}</div>
                @if(isset($contract->company->settings->address1))
                    <div class="party-detail">{{ $contract->company->settings->address1 }}</div>
                @endif
            </div>
            @if($contract->client)
            <div class="party">
                <div class="party-label">Client</div>
                <div class="party-name">{{ $contract->client->name }}</div>
                @if($contract->contact)
                    <div class="party-detail">{{ $contract->contact->first_name }} {{ $contract->contact->last_name }}</div>
                    <div class="party-detail">{{ $contract->contact->email }}</div>
                @endif
            </div>
            @endif
        </div>
    </div>

    <div class="section-label">Agreement</div>
    <div class="contract-body">{!! $renderedBody !!}</div>

    @if($contract->signers->count() > 0)
    <hr class="divider">
    <div class="signature-section">
        <div class="section-label">Signatures</div>
        <div class="sig-grid">
            @foreach($contract->signers as $signer)
            <div class="sig-block">
                @if($signer->isSigned() && $signer->signature_data)
                    <div class="sig-image-wrap">
                        <img src="{{ $signer->signature_data }}" alt="Signature">
                    </div>
                @else
                    <div style="height:80px;border:1px dashed #d1d5db;border-radius:6px;margin-bottom:6px;background:#f9fafb;"></div>
                @endif
                <div class="sig-line"></div>
                <div class="sig-name">{{ $signer->signed_name ?: $signer->name }}</div>
                @if($signer->signed_title)
                    <div class="sig-detail">{{ $signer->signed_title }}</div>
                @endif
                <div class="sig-detail">{{ $signer->email }}</div>
                @if($signer->signed_at)
                    <div class="sig-detail">Signed: {{ $signer->signed_at->format('M d, Y H:i') }} UTC</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($contract->notes)
    <hr class="divider">
    <div class="section-label">Notes</div>
    <div style="font-size:9pt;color:#374151;">{{ $contract->notes }}</div>
    @endif

    <div class="footer">
        This document was generated by {{ config('app.name') }} &nbsp;·&nbsp; {{ now()->format('Y-m-d H:i:s') }} UTC
        @if($contract->contract_hash) &nbsp;·&nbsp; Hash: {{ substr($contract->contract_hash,0,16) }}... @endif
    </div>
</div>

@if($isSigned)
<div class="certificate-page">
    <div class="cert-header">
        <div class="cert-title">Signature Certificate</div>
        <div class="cert-subtitle">Electronic Signature Audit Trail &amp; Verification Record</div>
    </div>

    <p style="font-size:9.5pt;color:#374151;margin-bottom:16px;">
        This certificate documents the binding electronic signatures applied to the document
        <strong>"{{ $contract->title }}"</strong> (Contract #{{ $contract->contract_number }}).
        This record is legally equivalent to a handwritten signature.
    </p>

    <div class="section-label" style="margin-bottom:6px;">Document Integrity</div>
    <div class="cert-hash">SHA-256: {{ $contract->contract_hash }}</div>

    <div class="section-label" style="margin:16px 0 8px;">Signer Details</div>
    <table class="audit-table">
        <thead>
            <tr>
                <th>Signer</th>
                <th>Email</th>
                <th>IP Address</th>
                <th>Signed At (UTC)</th>
                <th>Method</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contract->signers as $signer)
            <tr>
                <td>{{ $signer->signed_name ?: $signer->name }}</td>
                <td>{{ $signer->email }}</td>
                <td>{{ $signer->ip_address ?: '—' }}</td>
                <td>{{ $signer->signed_at ? $signer->signed_at->format('Y-m-d H:i:s') : '—' }}</td>
                <td>{{ $signer->signature_type ?: 'drawn' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-label" style="margin:20px 0 8px;">Audit Log</div>
    <table class="audit-table">
        <thead>
            <tr>
                <th>Event</th>
                <th>Actor</th>
                <th>IP Address</th>
                <th>Timestamp (UTC)</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($auditLogs as $log)
            <tr>
                <td>{{ str_replace('_',' ',ucfirst($log->event)) }}</td>
                <td>{{ $log->actor_type }}</td>
                <td>{{ $log->ip_address ?: '—' }}</td>
                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->notes ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer" style="margin-top:24px;">
        Certificate generated by {{ config('app.name') }} &nbsp;·&nbsp; {{ now()->format('Y-m-d H:i:s') }} UTC
    </div>
</div>
@endif
</body>
</html>
