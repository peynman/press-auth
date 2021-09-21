<?php

return [
    // auth urls prefix
    'prefix' => 'api',

    // auth middlewares
    'middlewares' => [
        'throttle:60,1',
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
    ],

    // redirects for authentication events
    'redirects' => [
        'login' => '/signin',
        'logout' => '/logout',
        'home' => '/',
        'signup' => '/signup',
    ],

    // 2 step authentication settings
    'auth_2_step' => [
        'forced_roles' => [
            'super-role',
            'accounting',
            'studio-admin',
            'sale-manager',
        ],
        'use_sms' => true,
        'use_email' => false,
    ],

    // master password settings
    'master_password' => [
        'roles' => [],
        'password' => env('MASTER_CUSTOMER_PASSWORD', null),
    ],

    // limit active sessions for these accounts
    'limit_sessions' => [
        'customer' => 1,
        'student' => 1,
    ],

    // signup settings
    'signup' => [
        // sms signup
        'sms' => [
            'enabled' => true,
            'numbers_only' => true,
            'code_len' => 6,
            'default_author' => 1,
            'default_gateway' => null,

            // number of digits for phone number
            'phone_digits' => 11,
        ],

        // email signup
        'email' => [
            'enabled' => false,
            'numbers_only' => true,
            'code_len' => 6,
            'default_author' => 1,
            'default_gateway' => null,
        ],

        // social signup
        'social' => [
            'enabled' => false,
            'providers' => [
                'facebook',
                'tweeter',
                'github',
                'google',
            ],
        ],

        // default role when a customer is registering
        'default_role' => null,
        // default domain to use when registering users with external APIs
        'default_domain' => null,
        // form id to fill automatically when a customer has registered
        'autofill_form' => null,
    ],
];
