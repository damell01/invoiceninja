{!! $title !!}

{!! ctrans('texts.company_import_failure_body') !!}

@if(isset($whitelabel) && !$whitelabel)
{{ ctrans('texts.ninja_email_footer', ['site' => config('branding.website_url')]) }}
@endif
