<?php

namespace Larapress\Auth\Signin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

/**
 * Sign in users based on their registration domain.
 *
 * @group Authentication
 */
class SigninController extends Controller
{
    public static function registerRoutes()
    {
        Route::post('signin', '\\' . self::class . '@signin')
            ->name('users.any.signin');

        Route::match([Request::METHOD_POST, Request::METHOD_GET], 'logout', '\\' . self::class . '@logout')
            ->name('users.any.logout');
    }

    public static function registerPublicWebRoutes()
    {
        Route::match([Request::METHOD_POST, Request::METHOD_GET], 'logout', '\\' . self::class . '@logoutWeb');
    }

    /**
     * API Authenticate
     *
     * @param ISigninService $service
     * @param \Larapress\Auth\Signin\SigninRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws Exception
     *
     * @unauthenticated
     */
    public function signin(ISigninService $service, SigninRequest $request)
    {
        return response()->json(array_merge($service->signin($request), []));
    }

    /**
     * Logout API token
     *
     * @param ISigninService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(ISigninService $service)
    {
        return response()->json($service->logout());
    }

    /**
     * Logout Web user
     *
     * @param ISigninService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function logoutWeb(ISigninService $service)
    {
        $service->logout();
        return redirect('/');
    }
}
