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
        'throttle:60,1'
    ],

    'prefix' => 'api',

    'signup' => [
        'sms' => [
            'from' => 'Signup Service',
            'numbers_only' => true,
            'code_len' => 6,
            'default-author' => 1,
        ],

        'default-role' => 3,
    ],
];
