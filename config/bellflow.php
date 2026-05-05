<?php

return [
    'ui' => [
        'module_visibility_mode' => env('BELLFLOW_MODULE_VISIBILITY_MODE', 'allow_all'),
        'allowed_modules' => array_values(array_filter(array_map('trim', explode(',', env('BELLFLOW_ALLOWED_MODULES', 'contracts'))))),
        'hidden_modules' => array_values(array_filter(array_map('trim', explode(',', env('BELLFLOW_HIDDEN_MODULES', ''))))),
        'hidden_portal_items' => array_values(array_filter(array_map('trim', explode(',', env('BELLFLOW_HIDDEN_PORTAL_ITEMS', ''))))),
    ],
];
