<?php

use \Illuminate\Support\Facades\Route;
use Larapress\Auth\Signin\SigninController;
use Larapress\Auth\Signup\SignupEmailController;
use Larapress\Auth\Signup\SignupSMSController;
use Larapress\Auth\Signup\SignupSocialController;

Route::middleware(config('larapress.auth.middleware'))
    ->prefix(config('larapress.auth.prefix'))
    ->group(function () {
        SigninController::registerRoutes();
        if (config('larapress.auth.signup.sms.enabled')) {
            SignupSMSController::registerRoutes();
        }
        if (config('larapress.auth.signup.email.enabled')) {
            SignupEmailController::registerRoutes();
        }
        if (config('larapress.auth.signup.social.enabled')) {
            SignupSocialController::registerRoutes();
        }
    });
