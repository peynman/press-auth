<?php

namespace Larapress\Auth\Signup;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

/**
 * Signup users with email in a specific domain.
 *
 * @group Customer Registration
 */
class SignupEmailController extends Controller
{
    public static function registerRoutes()
    {
        // email based verification
        Route::post('signup/email/verify', '\\'.self::class.'@sendEmailVerifyCode')
            ->name('user.any.signup.email.verify');
        Route::post('signup/email/check/resolve', '\\'.self::class.'@resolveEmailVerifyCode')
            ->name('user.any.signup.email.resolve');
        Route::post('signup/email/check/register', '\\'.self::class.'@registerEmailVerifyCode')
            ->name('user.any.signup.email.register');
        Route::post('signup/email/check/reset', '\\'.self::class.'@resetPasswordEmailVerifyCode')
            ->name('user.any.signup.email.reset');
    }

    /**
     * Send EMail Verification
     *
     * @param ISignupService $service
     * @param VerifyPhoneRequest $request

     * @return \Illuminate\Http\Response
     *
     * @unauthenticated
     */
    public function sendEmailVerifyCode(ISignupService $service, VerifyRequest $request)
    {
    }


    /**
     * Verify Email
     *
     * @param ISignupService $service
     * @param VerifyPhoneCheckRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @unauthenticated
     */
    public function resolveEmailVerifyCode(ISignupService $service, VerifyCheckRequest $request)
    {
    }

    /**
     * Register New Account (Email)
     *
     * @param ISignupService $service
     * @param VerifyPhoneCheckRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @unauthenticated
     */
    public function registerEmailVerifyCode(ISignupService $service, SignupRequest $request)
    {
    }


    /**
     * Reset password (Email)
     *
     * @param ISignupService $service
     * @param VerifyPhoneCheckRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @unauthenticated
     */
    public function resetPasswordEmailVerifyCode(ISignupService $service, ResetPasswordRequest $request)
    {
    }
}
