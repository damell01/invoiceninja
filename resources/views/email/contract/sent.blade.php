@component('mail::message')
# Please Review and Sign Your Contract

Hi {{ $signerName }},

**{{ $companyName }}** has sent you a contract for your review and signature.

**Contract:** {{ $contractTitle }}  
**Contract #:** {{ $contractNumber }}
@if($expiresAt)
**Expires:** {{ $expiresAt }}
@endif

@component('mail::button', ['url' => $signingUrl, 'color' => 'primary'])
Review &amp; Sign Contract
@endcomponent

This link is unique to you. Do not share it with others.

@if($notes)
---
**Note from {{ $companyName }}:**

{{ $notes }}
@endif

If you have any questions, please contact us directly.

Thanks,  
{{ $companyName }}

<small style="color:#9ca3af;">If you cannot click the button, copy and paste this URL into your browser:<br>{{ $signingUrl }}</small>
@endcomponent
