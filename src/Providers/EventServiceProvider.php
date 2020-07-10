<?php

namespace Larapress\Auth\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Larapress\Auth\Signin\SigninEvent' => [
            'Larapress\Auth\Signin\SigninReport',
        ],
        'Larapress\Auth\Signup\SignupEvent' => [
            'Larapress\Auth\Signup\SignupReport',
        ]
    ];


    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
