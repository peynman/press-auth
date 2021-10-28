<?php

namespace Larapress\Auth\Services\Signin;

use Exception;
use Larapress\Auth\Services\Signin\Requests\SigninRequest;
use Larapress\Profiles\IProfileUser;

interface ISigninService
{
    /**
     * @param SigninRequest $request
     *
     * @return array
     *
     * @throws Exception
     */
    public function signin(SigninRequest $request);

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @return array
     */
    public function signinUser(IProfileUser $user);

    /**
     * Undocumented function
     *
     * @param string|Domain $domain
     * @param string $username
     * @param string $password
     *
     * @return array
     *
     * @throws Exception
     */
    public function signinCredentials($domain, string $username, string $password);


    /**
     * Undocumented function
     *
     * @param string $phone
     * @param string $code
     * @return array
     */
    public function signinWithOTC($phone, $code);

    /**
     * Undocumented function
     *
     * @param string $phone
     * @return array
     */
    public function sendSigninOTC($phone);

    /**
     * @return array
     */
    public function logout();

    /**
     * Undocumented function
     *
     * @return array
     */
    public function refreshToken();
}
