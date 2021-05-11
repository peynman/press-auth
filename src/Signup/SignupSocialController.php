<?php

namespace Larapress\Auth\Signup;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

/**
 * Signup users with social login (github/facebook/...)
 *
 * @group Customer Registration
 */
class SignupSocialController extends Controller
{
    public static function registerRoutes()
    {
        // socialite verification
        Route::get('signup/socialite/verify/{driver}', '\\'.self::class.'@sendToSocialiteDriver')
            ->name('user.any.signup.socialite.verify');
    }

    /**
     * Undocumented function
     *
     * @param ISignupService $service

     * @return \Illuminate\Http\Response
     *
     * @unauthenticated
     */
    public function sendToSocialiteDriver(ISignupService $service)
    {
    }
}
