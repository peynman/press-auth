<?php

namespace Larapress\Auth\Signin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

class SigninController extends Controller
{
    public static function registerRoutes() {
        Route::post('signin', '\\'.self::class.'@signin')
            ->name('users.any.signin');

        Route::any('logout', '\\'.self::class.'@logout')
            ->name('users.any.logout');
    }

    public static function registerPublicWebRoutes() {
        Route::any('logout', function (ISigninService $service) {
            $service->logout();
            return redirect('/');
        });
    }
    /**
     * @param ISigninService $service
     * @param \Larapress\Auth\Signin\SigninRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Larapress\Core\Exceptions\AppException
     */
    public function signin(ISigninService $service, SigninRequest $request) {
        return response()->json(array_merge($service->signin($request), [
        ]));
    }

    /**
     * @param ISigninService $service
     * @return \Illuminate\Http\Response
     */
    public function logout(ISigninService $service, Request $request) {
        return response()->json($service->logout());
    }
}
