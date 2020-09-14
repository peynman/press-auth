<?php

namespace Larapress\Auth\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Larapress\Profiles\Flags\UserDomainFlags;
use Larapress\Profiles\Models\Domain;
use Larapress\Profiles\Repository\Domain\IDomainRepository;

class MasterIdentifierUserProvider implements UserProvider
{
    /**
     * @var \Larapress\Profiles\Repository\Domain\IDomainRepository
     */
    private $domainRepository;

    /**
     * MasterIdentifierProvider constructor.
     *
     * @param \Larapress\Profiles\Repository\Domain\IDomainRepository $domainRepository
     */
    public function __construct(IDomainRepository $domainRepository)
    {
        $this->domainRepository = $domainRepository;
    }

    /**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed $identifier
	 *
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveById( $identifier )
	{
		$userClass = config('larapress.crud.user.class');
		return $userClass::find($identifier);
	}

	/**
	 * Retrieve a user by their unique identifier and "remember me" token.
	 *
	 * @param  mixed  $identifier
	 * @param  string $token
	 *
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByToken( $identifier, $token )
	{
		$userClass = config('larapress.crud.user.class');
		return $userClass::where('id', $identifier)->where('remember_token', $token)->first();
	}

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Illuminate\Contracts\Auth\Authenticatable $user
	 * @param  string                                     $token
	 *
	 * @return void
	 */
	public function updateRememberToken( Authenticatable $user, $token )
	{
		$user->update([
			'remember_token' => $token,
		]);
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array $credentials
	 *
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByCredentials( array $credentials )
	{
	    /** @var \Illuminate\Database\Eloquent\Builder $query */
		$query = null;
		$user = null;

        $userClass = config('larapress.crud.user.class');
		$domain = $this->domainRepository->getCurrentRequestDomain();
        $query = $userClass::where(function($q) use($domain) {
            $q->orWhereHas('domains', function(Builder $q) use($domain) {
                $q->where('id', $domain->id)->where('user_domain.flags', '&', UserDomainFlags::REGISTRATION_DOMAIN);
            })->orWhereHas('roles', function($q) {
                $q->whereIn('name', config('larapress.profiles.security.roles.super-role'));
            });
        });

        $inPhones = clone $query;
        $user = $inPhones->whereHas('phones', function(Builder $q) use($credentials) {
            $q->where('number', $credentials['username']);
        })->first();

        if (is_null($user)) {
            $user = $query->where('name', $credentials['username'])->first();
        }

		return $user;
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Illuminate\Contracts\Auth\Authenticatable $user
	 * @param  array                                      $credentials
	 *
	 * @return bool
	 */
	public function validateCredentials( Authenticatable $user, array $credentials )
	{
		if (isset($credentials['password'])) {
			return Hash::check($credentials['password'], $user->password);
		}
		return false;
	}
}
