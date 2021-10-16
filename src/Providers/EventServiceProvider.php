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
        'Larapress\Auth\Services\Signin\SigninEvent' => [
            'Larapress\Auth\Services\Signin\Reports\SigninListener',
        ],
        'Larapress\Auth\Services\Signup\SignupEvent' => [
            'Larapress\Auth\Services\Signup\Reports\SignupListener',
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
