<?php

namespace Larapress\Auth\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Larapress\Auth\Signin\DomainISigninService;
use Larapress\Auth\Signin\ISigninService;
use Larapress\Profiles\Repository\Domain\IDomainRepository;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ISigninService::class, DomainISigninService::class);
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
    }
}
