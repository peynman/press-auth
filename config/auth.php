<?php

return [
    'prefix' => 'api',
    'middleware' => [
        'throttle:60,1',
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
    ],

    'redirects' => [
        'login' => '/signin',
        'logout' => '/logout',
        'home' => '/',
        'signup' => '/signup',
    ],

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

    // limit active sessions for these accounts
    'limit_sessions' => [
        'customer' => 1,
        'student' => 1,
    ],

    'signup' => [
        'sms' => [
            'enabled' => true,
            'numbers_only' => true,
            'code_len' => 6,
            'default_author' => 1,
            'default_gateway' => null,
        ],

        'email' => [
            'enabled' => false,
            'numbers_only' => true,
            'code_len' => 6,
            'default_author' => 1,
            'default_gateway' => null,
        ],

        'social' => [
            'enabled' => false,
            'providers' => [
                'facebook',
                'tweeter',
                'github',
                'google',
            ],
        ],

        'default_role' => null,
        'default_domain' => null,
        'autofill_form' => null,
    ],
];
