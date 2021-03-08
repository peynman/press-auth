<?php

return [
    'redirects' => [
        'login' => '/signin',
        'logout' => '/logout',
        'home' => '/',
        'signup' => '/signup',
    ],

    'middleware' => [
        'throttle:60,1',
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
    ],

    'prefix' => 'api',

    'routes' => [
        'signin' => 'signin',
        'signup' => 'signup',
    ],

    // force 2 step (sms verification) on login for these accounts
    'force_2_step_auth' => [
        'super-role',
        'accounting',
        'studio-admin',
        'sale-manager',
    ],

    // limit active sessions for these accounts
    'limit_sessions' => [
        'customer' => 1,
        'student' => 1,
    ],

    'signup' => [
        'sms' => [
            'numbers_only' => true,
            'code_len' => 6,
            'default_author' => 1,
            'default_gateway' => null,
        ],

        'default_role' => null,
        'autofill_form' => null,
    ],
];
