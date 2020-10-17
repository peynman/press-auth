<?php

return [
    'signin' => [
        'success' => 'شما با نام کاربری خود وارد سایت شدید',
    ],
    'logout' => [
        'success' => 'شما از نام کاربری خود خارج شدید',
    ],
    'signup' => [
        'messages' => [
            'signup_code' => ':code',
            'code_sent' => 'کد تایید برای شما پیامک شد، لطفا کد را در قسمت پایین وارد و کلید تایید را بزنید',
            'verify_success' => 'شماره شما با موفقیت تایید شد. با ثبت رمز و نام کاربری ثبت نام خود را کامل کنید',
            'verify_failed' => 'این کد صحیح نیست',
            'already_exist' => 'یک اکانت کاربری با این شماره در سیستم ثبت است. آیا مایل به بازیابی رمز هستید؟',
            'from' => 'SMS Service',
        ],
    ],

    'exceptions' => [
        'no_gateway' => "Could not send phone verify sms, no GatewayData was found"
    ]
];
