<?php

namespace Larapress\Auth\Signup;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

/**
 * @group User Registration
 *
 * Signup users with email or phone number in a specific domain.
 */
class SignupController extends Controller
{
    public static function registerRoutes()
    {
        Route::post('signup/sms/verify', '\\'.self::class.'@sendSMSVerifyCode')
            ->name('user.any.signup.sms.verify');

        Route::post('signup/sms/check/resolve', '\\'.self::class.'@resolveSMSVerifyCode')
            ->name('user.any.signup.sms.resolve');

        Route::post('signup/sms/check/register', '\\'.self::class.'@registerSMSVerifyCode')
            ->name('user.any.signup.sms.register');

        Route::post('signup/sms/check/reset', '\\'.self::class.'@resetPasswordSMSVerifyCode')
            ->name('user.any.signup.sms.reset');
    }

    /**
     * @param ISignupService $service
     * @param VerifyPhoneRequest $request
     * @return \Illuminate\Http\Response
     */
    public function sendSMSVerifyCode(ISignupService $service, VerifyPhoneRequest $request)
    {
        return response()->json($service->sendPhoneVerifySMS($request->getPhone()));
    }


    /**
     * Verify SMS Phone number and return the msg_id if its verified
     *   the id is used in reset password or registration
     *
     * @param ISignupService $service
     * @param VerifyPhoneCheckRequest $request
     * @return \Illuminate\Http\Response
     */
    public function resolveSMSVerifyCode(ISignupService $service, VerifyPhoneCheckRequest $request)
    {
        return response()->json($service->resolveSignUpWithPhoneVerifySMS($request->getPhone(), $request->getCode()));
    }


    /**
     * use msg_id
     *   the id is used in reset password or registration
     *
     * @param ISignupService $service
     * @param VerifyPhoneCheckRequest $request
     * @return \Illuminate\Http\Response
     */
    public function registerSMSVerifyCode(ISignupService $service, SignupRequest $request)
    {
        return response()->json($service->signupWithPhoneNumber($request));
    }


    /**
     * use msg_id
     *   the id is used in reset password or registration
     *
     * @param ISignupService $service
     * @param VerifyPhoneCheckRequest $request
     * @return \Illuminate\Http\Response
     */
    public function resetPasswordSMSVerifyCode(ISignupService $service, ResetPasswordRequest $request)
    {
        return response()->json($service->resetWithPhoneNumber(
            $request,
            $request->getPhone(),
            $request->getMessageID(),
            $request->getPassword()
        ));
    }
}
