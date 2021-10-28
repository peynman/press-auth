<?php

namespace Larapress\Auth\Services\Signin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Larapress\Auth\Services\Signin\Requests\OTCSendRequest;
use Larapress\Auth\Services\Signin\Requests\SigninRequest;
use Larapress\Auth\Services\Signin\Requests\OTCCheckRequest;

/**
 * Sign in users based on their registration domain.
 *
 * @group Authentication
 */
class SigninController extends Controller
{
    public static function registerPublicApiRoutes()
    {
        Route::post('signin', '\\' . self::class . '@signin')
            ->name('users.any.signin');

        Route::post('signin/send-otc', '\\' . self::class . '@sendOTC')
            ->name('users.any.signin.otc');
        Route::post('signin/verify-otc', '\\' . self::class . '@verifyOTC')
            ->name('users.any.signin.otc.verify');

        Route::post('signin/refresh-token', '\\' . self::class . '@refreshToken')
            ->name('users.any.refresh');
    }

    public static function registerApiRoutes()
    {
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
        return $service->signin($request);
    }

    /**
     * Undocumented function
     *
     * @param ISigninService $service
     * @param OTCSendRequest $request
     * @return \Illuminate\Http\Response
     *
     * @unauthenticated
     */
    public function sendOTC(ISigninService $service, OTCSendRequest $request)
    {
        return $service->sendSigninOTC($request->getPhone());
    }

    /**
     * Undocumented function
     *
     * @param ISigninService $service
     * @param OTCCheckRequest $request
     * @return \Illuminate\Http\Response
     *
     * @unauthenticated
     */
    public function verifyOTC(ISigninService $service, OTCCheckRequest $request)
    {
        return $service->signinWithOTC(
            $request->getPhone(),
            $request->getCode()
        );
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
        return $service->logout();
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

    /**
     * Refresh JWT token
     *
     * @param ISigninService $service
     *
     * @return \Illuminate\Http\Response
     */
    public function refreshToken(ISigninService $service)
    {
        return $service->refreshToken();
    }
}
