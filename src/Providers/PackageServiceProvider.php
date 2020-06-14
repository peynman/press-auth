<?php

namespace Larapress\Auth\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Larapress\Auth\Signin\DomainISigninService;
use Larapress\Auth\Signin\ISigninService;
use Larapress\Profiles\Repository\Domain\IDomainRepository;
use \Illuminate\Support\Facades\Event;
use Larapress\Auth\Signin\SigninEvent;
use Larapress\Auth\Signin\SigninReport;
use Larapress\Auth\Signup\DomainSignupService;
use Larapress\Auth\Signup\ISignupService;

class PackageServiceProvider extends ServiceProvider
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
    ];
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ISigninService::class, DomainISigninService::class);
        $this->app->bind(ISignupService::class, DomainSignupService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'larapress');
	    $this->loadRoutesFrom(__DIR__.'/../../routes/auth.php');
	    $this->loadMigrationsFrom(__DIR__.'/../../migrations');

        $this->publishes([
            __DIR__.'/../../config/auth.php' => config_path('larapress/auth.php'),
        ], ['config', 'larapress', 'larapress-auth']);

        Auth::provider('larapress', function($app, array $config) {
            return new MasterIdentifierUserProvider(app(IDomainRepository::class));
        });

        Event::listen(
            SigninEvent::class,
            SigninReport::class
        );
    }
}
