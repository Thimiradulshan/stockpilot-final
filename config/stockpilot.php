<?php

return [


    'document_date_lookback_days' => (int) env(
        'STOCKPILOT_DOCUMENT_LOOKBACK_DAYS',
        365
    ),


    'business' => [
        'name' => (string) env('BUSINESS_NAME', 'StockPilot Business'),
        'address' => (string) env('BUSINESS_ADDRESS', ''),
        'phone' => (string) env('BUSINESS_PHONE', ''),
        'email' => (string) env('BUSINESS_EMAIL', ''),
        'tax_number' => (string) env('BUSINESS_TAX_NUMBER', ''),
    ],


    'default_admin' => [
        'name' => (string) env(
            'DEFAULT_ADMIN_NAME',
            'System Administrator'
        ),
        'email' => (string) env(
            'DEFAULT_ADMIN_EMAIL',
            'admin@stockpilot.app'
        ),
        'password' => (string) env('DEFAULT_ADMIN_PASSWORD', 'password'),
    ],

    'seed_demo' => (bool) env('SEED_DEMO', false),


    'walk_in_customer_name' => (string) env(
        'STOCKPILOT_WALK_IN_CUSTOMER',
        'Walk-in Customer'
    ),

];
