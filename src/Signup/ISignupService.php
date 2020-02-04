<?php

namespace Larapress\Auth\Signup;

interface ISignupService
{
    /**
     * @param \Larapress\Auth\Signup\SignupRequest $request
     * @return array
     */
    public function signup(SignupRequest $request);
}