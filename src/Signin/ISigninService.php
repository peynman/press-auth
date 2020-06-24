<?php

namespace Larapress\Auth\Signin;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Larapress\Auth\Signin\SigninRequest;
use Larapress\Profiles\IProfileUser;

interface ISigninService
{
    /**
     * @param SigninRequest $request
     * @param string $guard
     * @return \Larapress\Auth\Signin\SigninResponse
     * @throws \Larapress\Core\Exceptions\AppException
     */
    public function signin(SigninRequest $request);


    /**
     * @return \Larapress\Auth\Signin\SigninResponse
     * @throws \Larapress\Core\Exceptions\AppException
     */
    public function signinUser(string $username, String $password);

    /**
     * @return array
     */
    public function logout();
}
