<?php

use Illuminate\Support\Facades\Route;
use Larapress\Auth\Signin\SigninController;
use Larapress\Auth\Signup\SignupEmailController;
use Larapress\Auth\Signup\SignupSMSController;
use Larapress\Auth\Signup\SignupSocialController;

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
