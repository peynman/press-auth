<?php

namespace Larapress\Auth\Signin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

class SigninController extends Controller
{
    public static function registerRoutes() {
        Route::post('signin', '\\'.self::class.'@signin')
            ->name('user.any.signin');

        Route::post('logout', '\\'.self::class.'@logout')
            ->name('user.any.logout');
    }

    /**
     * @param ISigninService $service
     * @param \Larapress\Auth\Signin\SigninRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Larapress\Core\Exceptions\AppException
     */
    public function signin(ISigninService $service, SigninRequest $request) {
        return response()->json($service->signin($request));
    }

    /**
     * @param ISigninService $service
     * @return \Illuminate\Http\Response
     */
    public function logout(ISigninService $service) {
        return response()->json($service->logout());
    }
}