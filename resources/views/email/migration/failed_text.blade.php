{!! ctrans('texts.migration_failed_label') !!}

{!! ctrans('texts.migration_failed') !!} {!! $company->present()->name() !!}

@if(\App\Utils\Ninja::isSelfHost() || $is_system)
    {!! $exception->getMessage() !!}
    {!! $content !!}
@else
    Please contact us at {{ config('branding.support_email') }} for more information on this error.
@endif

