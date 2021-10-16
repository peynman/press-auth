<?php

use Illuminate\Support\Facades\Route;
use Larapress\Auth\Services\Signin\SigninController;
use Larapress\Auth\Services\Signup\Controllers\SignupEmailController;
use Larapress\Auth\Services\Signup\Controllers\SignupSMSController;
use Larapress\Auth\Services\Signup\Controllers\SignupSocialController;

Route::middleware(config('larapress.crud.middlewares'))
    ->prefix(config('larapress.crud.prefix'))
    ->group(function () {
        SigninController::registerApiRoutes();
    });

Route::middleware(config('larapress.crud.public-middlewares'))
    ->prefix(config('larapress.crud.prefix'))
    ->group(function () {
        SigninController::registerPublicApiRoutes();

        if (config('larapress.auth.signup.sms.enabled')) {
            SignupSMSController::registerPublicApiRoutes();
        }
        if (config('larapress.auth.signup.email.enabled')) {
            SignupEmailController::registerPublicApiRoutes();
        }
        if (config('larapress.auth.signup.social.enabled')) {
            SignupSocialController::registerPublicApiRoutes();
        }
    });
