@component('email.template.master', ['design' => 'light', 'settings' => $settings])
    @slot('header')
        @include('email.components.header', ['logo' => asset(config('branding.logo_dark'))])
    @endslot

    <h1>Payment for your invoice has been completed!</h1>
    <p>We want to inform you that payment was completed for your invoice.</p>

    <a href="{{ config('branding.website_url') }}" target="_blank" class="button">Visit {{ config('branding.company_name') }}</a>
@endcomponent
