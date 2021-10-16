<?php

namespace Larapress\Auth\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Larapress\Auth\Services\Signup\DomainSignupService;
use Larapress\Auth\Services\Signup\ISignupService;
use Larapress\Auth\Services\Signin\DomainSigninService;
use Larapress\Auth\Services\Signin\ISigninService;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ISigninService::class, DomainSigninService::class);
        $this->app->bind(ISignupService::class, DomainSignupService::class);

        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'larapress');
        $this->loadMigrationsFrom(__DIR__.'/../../migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        $this->publishes([
            __DIR__.'/../../config/auth.php' => config_path('larapress/auth.php'),
        ], ['config', 'larapress', 'larapress-auth']);

        Auth::provider('larapress', function () {
            return new MasterIdentifierUserProvider(
                app(\Larapress\Profiles\Repository\Domain\IDomainRepository::class)
            );
        });
    }
}
