<?php

return [
	'theme' => [
		'name' => null,
		'namespace' => 'larapress:auth'
	],

	'redirects' => [
		'login' => 'dashboard.any',
		'logout' => 'dashboard.login.view',
		'home' => 'home',
		'signup' => 'dashboard.login.view',
	],

    'middleware' => [
        'throttle:60,1',
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        'bindings',
    ],

    'prefix' => 'api',

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
