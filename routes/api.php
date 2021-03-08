<?php

use Illuminate\Support\Facades\Route;
use Larapress\Auth\Password\UpdatePasswordController;
use Larapress\Auth\Signin\SigninController;

Route::middleware(config('larapress.crud.middlewares'))
    ->prefix(config('larapress.crud.prefix'))
    ->group(function () {
        UpdatePasswordController::registerRoutes();
    });

Route::middleware(config('larapress.pages.middleware'))
    ->prefix(config('larapress.pages.prefix'))
    ->group(function () {
        SigninController::registerPublicWebRoutes();
    });
