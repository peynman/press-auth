<?php

namespace Larapress\Auth\Tests\Feature;

use Larapress\CRUD\Tests\PackageTestApplication;

class SigninTest extends PackageTestApplication
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSignin()
    {
        // test invalid signin
        $this->postJson('api/'.config('larapress.auth.routes.signin'), [
            'username' => 'root',
            'password' => 'root',
        ])->assertStatus(400);

        // test success signin
        // $this->postJson('api/'.config('larapress.auth.routes.signin'), [
        //     'username' => 'root',
        //     'password' => 'root',
        // ])->assertStatus(200);
    }
}
