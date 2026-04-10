<?php

return [
    'app_name' => env('APP_NAME', 'DBell Billing'),
    'company_name' => env('BRAND_COMPANY_NAME', 'DBell Creations'),
    'browser_title' => env('BRAND_BROWSER_TITLE', env('APP_NAME', 'DBell Billing')),
    'support_email' => env('BRAND_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@dbellcreations.com')),
    'support_name' => env('BRAND_SUPPORT_NAME', env('MAIL_FROM_NAME', 'DBell Creations')),
    'website_url' => rtrim(env('BRAND_WEBSITE_URL', env('APP_URL', 'https://billing.dbellcreations.com')), '/'),
    'support_url' => env('BRAND_SUPPORT_URL', env('BRAND_WEBSITE_URL', env('APP_URL', 'https://billing.dbellcreations.com'))),
    'terms_url' => env('BRAND_TERMS_URL', env('APP_URL', 'https://billing.dbellcreations.com').'/terms'),
    'privacy_url' => env('BRAND_PRIVACY_URL', env('APP_URL', 'https://billing.dbellcreations.com').'/privacy'),
    'footer_link_text' => env('BRAND_FOOTER_LINK_TEXT', 'DBell Billing'),
    'footer_text' => env('BRAND_FOOTER_TEXT', 'Powered by DBell Creations'),
    'logo_light' => env('BRAND_LOGO_LIGHT', 'images/invoiceninja-black-logo-2.png'),
    'logo_dark' => env('BRAND_LOGO_DARK', 'images/invoiceninja-white-logo.png'),
    'app_logo' => env('APP_LOGO', env('BRAND_APP_LOGO', env('APP_URL', 'https://billing.dbellcreations.com').'/images/new_logo.png')),
    'favicon' => env('BRAND_FAVICON', 'favicon.png'),
    'enable_service_worker' => env('ENABLE_SERVICE_WORKER', false),
    'hostinger_app_folder' => env('HOSTINGER_APP_FOLDER', 'dbellbilling'),
];
