<?php

namespace Larapress\Auth\Signin;

use Larapress\Profiles\IProfileUser;
use Exception;

interface ISigninService
{
    /**
     * @param SigninRequest $request
     *
     * @return \Larapress\Auth\Signin\SigninResponse
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
     * @return \Larapress\Auth\Signin\SigninResponse
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
     * @param IProfileUser $user
     * @param string $old
     * @param string $new
     * @return void
     */
    public function updatePassword($user, string $old, string $new);
}
