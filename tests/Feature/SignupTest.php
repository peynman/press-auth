<?php

namespace Larapress\Auth\Tests\Feature;

use Larapress\CRUD\Tests\PackageTestApplication;

class SignupTest extends PackageTestApplication
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSignup()
    {
        // test invalid signup
        $this->postJson(config('larapress.auth.prefix').'/'.config('larapress.auth.routes.signup'), [
            'username' => 'root',
            'password' => 'root',
        ])->assertStatus(400);
    }
}
