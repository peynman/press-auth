<?php

namespace Larapress\Auth\Signup;

interface ISignupService
{
    /**
     * Undocumented function
     *
     * @param string $phone
     * @param string $msgId
     * @param string $username
     * @param string $password
     * @return array
     */
    public function signupWithPhoneNumber(string $phone, string $msgId, string $username, string $password);


    /**
     * Undocumented function
     *
     * @param string $phone
     * @param string $msgId
     * @param string $password
     * @return array
     */
    public function resetWithPhoneNumber(string $phone, string $msgId, string $password);

    /**
     * Undocumented function
     *
     * @param string $phone
     * @return array
     */
    public function sendPhoneVerifySMS(string $phone);

    /**
     * Undocumented function
     *
     * @param string $phone
     * @param string $code
     * @return array
     */
    public function verifyPhoneSMS(string $phone, string $code);


    /**
     * Undocumented function
     *
     * @param string $phone
     * @param string $code
     * @return array
     */
    public function resolveSignUpWithPhoneVerifySMS(string $phone, string $code);

    /**
     * Undocumented function
     *
     * @param String $email
     * @return void
     */
    public function sendEmailVerify(string $email);

    /**
     * Undocumented function
     *
     * @param String $email
     * @param String $code
     * @return void
     */
    public function verifyEmail(string $email, string $code);
}
