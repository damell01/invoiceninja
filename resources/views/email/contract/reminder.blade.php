@component('mail::message')
# Reminder: Contract Awaiting Your Signature

Hi {{ $signerName }},

This is a friendly reminder that the following contract is still awaiting your signature:

**Contract:** {{ $contractTitle }}  
**Contract #:** {{ $contractNumber }}
@if($expiresAt)
**Expires:** {{ $expiresAt }}
@endif

@component('mail::button', ['url' => $signingUrl, 'color' => 'primary'])
Sign Contract Now
@endcomponent

If you have any questions or concerns about this contract, please contact us.

Thanks,  
{{ $companyName }}

<small style="color:#9ca3af;">If you cannot click the button, copy and paste this URL into your browser:<br>{{ $signingUrl }}</small>
@endcomponent
