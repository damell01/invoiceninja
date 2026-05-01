@component('mail::message')
@if($isWarning)
# Contract Expiring Soon

Hi {{ $signerName }},

The following contract will expire soon and still requires your signature:

**Contract:** {{ $contractTitle }}  
**Contract #:** {{ $contractNumber }}  
**Expires:** {{ $expiresAt }}

@component('mail::button', ['url' => $signingUrl, 'color' => 'error'])
Sign Before It Expires
@endcomponent

@else
# Contract Has Expired

Hi,

The following contract has expired without being fully signed:

**Contract:** {{ $contractTitle }}  
**Contract #:** {{ $contractNumber }}  
**Expired At:** {{ $expiresAt }}

Please reach out to your client if you wish to resend or create a new version.
@endif

Thanks,  
{{ $companyName }}
@endcomponent
