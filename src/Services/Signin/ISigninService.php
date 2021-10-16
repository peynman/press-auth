<?php

namespace Larapress\Auth\Services\Signin;

use Larapress\Profiles\IProfileUser;
use Exception;

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
     * @param string|Domain $domain
     * @param string $username
     * @param string $password
     *
     * @return array
     *
     * @throws Exception
     */
    public function signinUser($domain, string $username, string $password);

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
