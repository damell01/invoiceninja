@component('mail::message')
@if($isAdminCopy)
# Contract Signed — Admin Notification

Hi,

**{{ $signerName }}** has signed the contract **"{{ $contractTitle }}"** ({{ $contractNumber }}).

**Signed At:** {{ $signedAt }}

@if($allSigned)
✅ All parties have now signed. The contract is fully executed.
@else
⏳ Waiting on additional signatures.
@endif

@component('mail::button', ['url' => $adminUrl, 'color' => 'primary'])
View Contract
@endcomponent

@else
# Your Contract Has Been Signed

Hi {{ $signerName }},

Thank you for signing **"{{ $contractTitle }}"** ({{ $contractNumber }}).

**Signed At:** {{ $signedAt }}

@if($downloadUrl)
@component('mail::button', ['url' => $downloadUrl, 'color' => 'success'])
Download Signed Copy
@endcomponent
@endif

This is your official confirmation. Please keep this email for your records.
@endif

Thanks,  
{{ $companyName }}
@endcomponent
