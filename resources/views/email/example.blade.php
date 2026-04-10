@component('email.template.master', ['design' => 'light'])

@slot('header')
    @component('email.components.header', ['p' => 'Your upgrade has completed!', 'logo' => asset(config('branding.logo_light'))])
        Upgrade!
    @endcomponent

@endslot

@slot('greeting')
    Hello, David
@endslot

Hello, this is really tiny template. We just want to inform you that upgrade has been completed.

    @component('email.components.button', ['url' => config('branding.website_url'), 'show_link' => true])
    Visit InvoiceNinja
@endcomponent

@component('email.components.table')
| Laravel       | Table         | Example  |
| ------------- |:-------------:| --------:|
| Col 2 is      | Centered      | $10      |
| Col 3 is      | Right-Aligned | $20      |
@endcomponent

@slot('signature')
    DBell Creations Support ({{ config('branding.support_email') }})
@endslot

@slot('footer')
    @component('email.components.footer', ['url' => config('branding.website_url'), 'url_text' => '&copy; '.config('branding.company_name')])
        For any info, please visit InvoiceNinja.
    @endcomponent
@endslot

@slot('below_card')
    Lorem ipsum dolor sit amet. I love InvoiceNinja.
@endslot    

@endcomponent
