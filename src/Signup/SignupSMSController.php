<?php

namespace Larapress\Auth\Signup;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Larapress\Profiles\Repository\Domain\IDomainRepository;

/**
 * Signup users with mobile phone number in a specific domain.
 *
 * @group Customer Registration
 */
class SignupSMSController extends Controller
{
    public static function registerRoutes()
    {
        // sms based verification
        Route::post('signup/sms/verify', '\\' . self::class . '@sendSMSVerifyCode')
            ->name('user.any.signup.sms.verify');
        Route::post('signup/sms/check/resolve', '\\' . self::class . '@resolveSMSVerifyCode')
            ->name('user.any.signup.sms.resolve');
        Route::post('signup/sms/check/register', '\\' . self::class . '@registerSMSVerifyCode')
            ->name('user.any.signup.sms.register');
        Route::post('signup/sms/check/reset', '\\' . self::class . '@resetPasswordSMSVerifyCode')
            ->name('user.any.signup.sms.reset');
    }

    /**
     * Send Phone Verification
     *
     * @param ISignupService $service
     * @param VerifyPhoneRequest $request

     * @return \Illuminate\Http\Response
     *
     * @unauthenticated
     */
    public function sendSMSVerifyCode(IDomainRepository $repo, ISignupService $service, VerifyRequest $request)
    {
        return response()->json(
            $service->sendPhoneVerifySMS(
                $request->getPhone(),
                $repo->getRequestDomain($request)
            )
        );
    }


    /**
     * Verify Phone number
     *
     * @param ISignupService $service
     * @param VerifyPhoneCheckRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @unauthenticated
     */
    public function resolveSMSVerifyCode(IDomainRepository $repo, ISignupService $service, VerifyCheckRequest $request)
    {
        return response()->json(
            $service->resolveSignUpWithPhoneVerifySMS(
                $request->getPhone(),
                $request->getCode(),
                $repo->getRequestDomain($request)
            )
        );
    }

    /**
     * Register New Account (Phone)
     *
     * Complete a registration. You need to verify a phone number or email address first.
     *
     * @param ISignupService $service
     * @param VerifyPhoneCheckRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @unauthenticated
     */
    public function registerSMSVerifyCode(ISignupService $service, SignupRequest $request)
    {
        return response()->json(
            $service->signupWithPhoneNumber(
                $request
            )
        );
    }


    /**
     * Reset password (Phone)
     *
     * @param ISignupService $service
     * @param VerifyPhoneCheckRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @unauthenticated
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
