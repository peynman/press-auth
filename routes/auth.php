<?php

use \Illuminate\Support\Facades\Route;
use Larapress\Auth\Signin\SigninController;

Route::middleware(config('larapress.auth.middleware'))
	 ->prefix(config('larapress.auth.prefix'))
     ->group(function() {
         SigninController::registerRoutes();
     });