<?php

namespace Larapress\Auth\Signup;

use Illuminate\Http\Request;
use Larapress\Profiles\Models\Domain;

interface ISignupService
{
    /**
     * Undocumented function
     *
     * @param SignupRequest $request
     *
     * @return array
     */
    public function signupWithPhoneNumber(SignupRequest $service);

    /**
     * Undocumented function
     *
     * @param string $phone
     * @param string $msgId
     * @param string $password
     *
     * @return array
     */
    public function resetWithPhoneNumber(Request $request, string $phone, string $msgId, string $password);

    /**
     * Undocumented function
     *
     * @param string $phone
     * @param Domain|int $domain
     *
     * @return array
     */
    public function sendPhoneVerifySMS(string $phone, $domain);

    /**
     * Undocumented function
     *
     * @param string $phone
     * @param string $code
     *
     * @return array
     */
    public function verifyPhoneSMS(string $phone, string $code);


    /**
     * Undocumented function
     *
     * @param string $phone
     * @param string $code
     * @param string|Domain $domain
     *
     * @return array
     */
    public function resolveSignUpWithPhoneVerifySMS(string $phone, string $code, $domain);

    /**
     * Undocumented function
     *
     * @param String $email
     * @param string|Domain $domain
     *
     * @return void
     */
    public function sendEmailVerify(string $emai, $domain);

    /**
     * Undocumented function
     *
     * @param String $email
     * @param String $code
     *
     * @return void
     */
    public function verifyEmail(string $email, string $code);
}
