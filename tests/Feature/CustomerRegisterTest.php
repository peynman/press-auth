<?php

namespace Larapress\Auth\Tests\Feature;

use App\Models\User;
use Larapress\CRUD\Tests\PackageTestApplication;
use Larapress\Notifications\Models\SMSMessage;

class CustomerRegisterTest extends PackageTestApplication
{
    /**
     * @return void
     */
    public function testSMSSignupInvalidRequest()
    {
        // test invalid signin
        $this->postJson(config('larapress.auth.prefix') . '/signup/sms/verify', [
            'phone' => '989121111111',
        ])->assertStatus(400);
    }

    /**
     * @return void
     */
    public function testSMSSignupResolveInvalidCode()
    {
        // test valid sms verify send message
        $this->postJson(config('larapress.auth.prefix') . '/signup/sms/verify', [
            'phone' => '09121111111',
            'key' => '12345',
            'captcha' => '12345',
            'accept_terms' => true,
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
            ]);

        $this->postJson(config('larapress.auth.prefix') . '/signup/sms/check/resolve', [
            'phone' => '09121111111',
            'code' => 'somewrongcode',
        ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'message',
            ]);
    }


    /**
     * @return void
     */
    public function testSMSSignupResolveInvalidPhone()
    {
        // test valid sms verify send message
        $this->postJson(config('larapress.auth.prefix') . '/signup/sms/verify', [
            'phone' => '09121111111',
            'key' => '12345',
            'captcha' => '12345',
            'accept_terms' => true,
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
            ]);

        $this->postJson(config('larapress.auth.prefix') . '/signup/sms/check/resolve', [
            'phone' => '091211112324',
            'code' => 'somewrongcode',
        ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'message',
            ]);
    }

    /**
     * @return void
     */
    public function testSMSSignupSuccess()
    {
        // test valid sms verify send message
        $this->postJson(config('larapress.auth.prefix') . '/signup/sms/verify', [
            'phone' => '09121111111',
            'key' => '12345',
            'captcha' => '12345',
            'accept_terms' => true,
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
            ]);

        // test sms verify resolving with correct code
        $code = SMSMessage::where('to', '09121111111')->first();
        $response = $this->postJson(config('larapress.auth.prefix') . '/signup/sms/check/resolve', [
            'phone' => '09121111111',
            'code' => $code->data['code'],
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'msg_id',
                'status'
            ])
            ->decodeResponseJson();

        $response = $this->postJson(config('larapress.auth.prefix') . '/signup/sms/check/register', [
            'phone' => '09121111111',
            'code' => $code->data['code'],
            'msg_id' => $response['msg_id'],
            'username' => 'customername',
            'password' => 'customerpass',
            'password_confirmation' => 'customerpass',
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                ],
                'tokens' => [
                    'api',
                    'web'
                ],
            ])->decodeResponseJson();

        $customer = User::with(['roles', 'domains', 'phones', 'emails'])->find($response['user']['id']);
        $this->assertNotNull($customer);
        $this->assertEquals($customer->roles[0]->id, config('larapress.profiles.customer_role_id'));
    }
}
