<?php

return [
    'app_name' => env('APP_NAME', 'Bellflow'),
    'company_name' => env('BRAND_COMPANY_NAME', 'Bellflow'),
    'browser_title' => env('BRAND_BROWSER_TITLE', env('APP_NAME', 'Bellflow')),
    'support_email' => env('BRAND_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@bellflow.app')),
    'support_name' => env('BRAND_SUPPORT_NAME', env('MAIL_FROM_NAME', 'Bellflow')),
    'website_url' => rtrim(env('BRAND_WEBSITE_URL', env('APP_URL', 'http://localhost')), '/'),
    'support_url' => env('BRAND_SUPPORT_URL', env('BRAND_WEBSITE_URL', env('APP_URL', 'http://localhost'))),
    'terms_url' => env('BRAND_TERMS_URL', rtrim(env('APP_URL', 'http://localhost'), '/').'/terms'),
    'privacy_url' => env('BRAND_PRIVACY_URL', rtrim(env('APP_URL', 'http://localhost'), '/').'/privacy'),
    'footer_link_text' => env('BRAND_FOOTER_LINK_TEXT', 'Bellflow'),
    'footer_text' => env('BRAND_FOOTER_TEXT', 'Powered by Bellflow'),
    'logo_light' => env('BRAND_LOGO_LIGHT', 'images/bellflow-logo.svg'),
    'logo_dark' => env('BRAND_LOGO_DARK', 'images/bellflow-logo.svg'),
    'app_logo' => env('APP_LOGO', env('BRAND_APP_LOGO', env('APP_URL', 'http://localhost').'/images/bellflow-logo.svg')),
    'favicon' => env('BRAND_FAVICON', 'images/bellflow-favicon.svg'),
    'enable_service_worker' => env('ENABLE_SERVICE_WORKER', false),
    'hostinger_app_folder' => env('HOSTINGER_APP_FOLDER', 'bellflow'),
];
