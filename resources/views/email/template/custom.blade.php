{!! $body !!}
@isset($whitelabel)
    @if(!$whitelabel)
        <table cellpadding="0" cellspacing="0" width="100%">
           <tr>
	            <td>
	                <p>
	                    <a href="{{ config('branding.website_url') }}" target="_blank">
	                        {{ __('texts.ninja_email_footer', ['site' => config('branding.company_name')]) }}
	                    </a>
	                </p>
	            </td>
            </tr>
        </table>
    @endif
@endif
