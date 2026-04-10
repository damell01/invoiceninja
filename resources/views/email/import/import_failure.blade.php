@component('email.template.master', ['design' => 'light', 'settings' => $settings])

    @slot('header')
        @include('email.components.header', ['logo' => $logo])
    @endslot

    <h2>{{ $title }}</h2>

    <p>{{ctrans('texts.company_import_failure_body')}}</p>

    @if($user_message)
    <p>{{ $user_message }}</p>
    @endif

    @if(isset($whitelabel) && !$whitelabel)
        @slot('footer')
            @component('email.components.footer', ['url' => config('branding.website_url'), 'url_text' => '&copy; '.config('branding.company_name')])
                For any info, please visit InvoiceNinja.
            @endcomponent
        @endslot
    @endif
@endcomponent
