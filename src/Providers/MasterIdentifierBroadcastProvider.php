<?php

namespace Larapress\Auth\Providers;

use Illuminate\Support\Facades\Broadcast;
use Larapress\CRUD\Middleware\CRUDAuthorizeRequest;
use Illuminate\Support\ServiceProvider;

class MasterIdentifierBroadcastProvider extends ServiceProvider
{
    public function boot()
    {
        Broadcast::channel('crud.{name}.{verb}', function ($user, $name, $verb) {
            $permissions = CRUDAuthorizeRequest::getCRUDVerbPermissions($name, $verb);
            return $user->hasPermission($permissions);
        });

        Broadcast::channel('domain.{domain_id}.user.{user_id}.*', function ($user, $domain_id, $user_id) {
            if ($user->hasRole(config('larapress.profiles.security.roles.super-user'))) {
                return true;
            }

            if ($user->hasRole(config('larapress.profiles.security.roles.affiliate'))) {
                return in_array($domain_id, $user->getAffiliateDomainIds());
            }

            if ($user->hasRole(config('larapress.profiles.security.roles.customer'))) {
                return $user_id === $user->id;
            }

            return false;
        });
    }
}
