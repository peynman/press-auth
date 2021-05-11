<?php

namespace Larapress\Auth\Tests\Feature;

use Larapress\CRUD\Tests\PackageTestApplication;

class AdminSigninTest extends PackageTestApplication
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAdminSignin()
    {
        // test invalid signin
        $this->postJson('api/signin', [
            'username' => 'root',
            'password' => 'root',
        ])->assertStatus(400);

        // test success signin
        // captcha already mocked in PackageTestApplication
        $this->postJson('api/signin', [
            'username' => 'root',
            'password' => 'root',
            'key' => '12345',
            'captcha' => '12345'
        ])->assertStatus(200);
    }
}
