<?php

namespace Larapress\Auth\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Larapress\CRUD\Tests\CustomerTestApplication;
use Larapress\Notifications\Models\SMSMessage;

class SignupReportTest extends CustomerTestApplication
{
    use WithFaker;

    /**
     * @return void
     */
    public function testSMSSignupReports()
    {
        $totalUsersSignup = 20;

        for ($i = 0; $i < $totalUsersSignup; $i++) {
            $this->travel(3)->minutes();

            $phoneNumber = '0912'.$this->faker->numberBetween(1000000, 9999999);
            $username = $this->faker->userName.$this->faker->userName; // min length of 6
            // test valid sms verify send message
            $this->postJson(config('larapress.auth.prefix') . '/signup/sms/verify', [
                'phone' => $phoneNumber,
                'key' => '12345',
                'captcha' => '12345',
                'accept_terms' => true,
            ])
                ->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                ]);

            // test sms verify resolving with correct code
            $code = SMSMessage::where('to', $phoneNumber)->first();
            $response = $this->postJson(config('larapress.auth.prefix') . '/signup/sms/check/resolve', [
                'phone' => $phoneNumber,
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
                'phone' => $phoneNumber,
                'code' => $code->data['code'],
                'msg_id' => $response['msg_id'],
                'username' => $username,
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
            $this->assertEquals($customer->roles[0]->id, config('larapress.auth.signup.default_role'));
        }

        $this->postJson('api/signin', [
            'username' => 'root',
            'password' => 'root',
            'key' => '12345',
            'captcha' => '12345'
        ])->assertStatus(200);
        $this->postJson('/api/' . config('larapress.profiles.routes.users.name') . '/reports', [
            'name' => 'metrics.windowed.signup',
            'groups' => ['domain'],
            'window' => '5m',
        ])
        ->dump()
        ->assertStatus(200)
        ->assertJsonCount(12);
    }
}
