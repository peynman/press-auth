<?php

namespace Larapress\Auth\Signin;

use Exception;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Larapress\CRUD\BaseFlags;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\Profiles\Flags\UserFlags;
use Larapress\Profiles\IProfileUser;
use Larapress\Profiles\Repository\Domain\IDomainRepository;

class DomainSigninService implements ISigninService
{
    /**
     * @var \Larapress\Profiles\Repository\Domain\IDomainRepository
     */
    private $domainRepository;

    /**
     * DomainSigninService constructor.
     *
     * @param \Larapress\Profiles\Repository\Domain\IDomainRepository $domainRepository
     */
    public function __construct(IDomainRepository $domainRepository)
    {
        $this->domainRepository = $domainRepository;
    }

    /**
     * @param \Larapress\Auth\Signin\SigninRequest $request
     * @return array
     * @throws \Larapress\Core\Exceptions\AppException
     */
    public function signin(SigninRequest $request)
    {
        return $this->signinUser($request->getUsername(), $request->getPassword());
    }

    /**
     * @return \Larapress\Auth\Signin\SigninResponse
     * @throws \Larapress\Core\Exceptions\AppException
     */
    public function signinUser(string $username, string $password)
    {
        $guards = config('auth.guards');
        $request = Request::createFromGlobals();
        foreach ($guards as $guardName => $guardParams) {
            /** @var \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard $guard */
            $guard = Auth::guard($guardName);
            $token = $guard->attempt([
                'username' => $username,
                'password' => $password,
            ], true);
            if ($token !== false) {
                if ($guard instanceof SessionGuard) {
                    $session = $request->getSession();
                    if (!is_null($session)) {
                        $session = $session->getName();
                    }
                    $token = [
                        'remember' => $guard->getRecallerName(),
                        'session' => $session,
                    ];
                }
                if (!is_null($token)) {
                    $guards[$guardName] = $token;
                }
            } else {
                throw new AppException(AppException::ERR_INVALID_CREDENTIALS);
            }
        }
        $user =  Auth::user();

        if (!is_null($user)) {
            if (BaseFlags::isActive($user->flags, UserFlags::BANNED)) {
                throw new AppException(AppException::ERR_ACCESS_BANNED);
            }

            /** @var IDdomainRepository */
            $domainRepo = app()->make(IDomainRepository::class);
            $domain = $domainRepo->getCurrentRequestDomain();
            SigninEvent::dispatch($user, $domain, $request->ip(), time());
        }

        return [
            'tokens' => $guards,
            'user' => $user,
            'message' => trans('larapress::auth.signin.success')
        ];
    }

    /**
     * @return array
     */
    public function logout()
    {
        $guards = config('auth.guards');
        foreach ($guards as $guardName => $guardParams) {
            /** @var \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard $guard */
            $guard = Auth::guard($guardName);
            try {
                $guard->logout();
            } catch (Exception $e) {
            }
        }

        return [
            'success' => true,
            'message' => trans(config('larapress.auth.theme.translations.namespace') . '::larapress.auth..logout.success')
        ];
    }


    /**
     * Undocumented function
     *
     * @param IProfileUser|ICRUDUser $user
     * @param string $old
     * @param string $new
     * @return void
     */
    public function updatePassword($user, string $old, string $new)
    {
        if (Hash::check($old, $user->password)) {
            $user->update([
                'password' => Hash::make($new),
            ]);
        } else {
            throw new AppException(AppException::ERR_INVALID_PARAMS);
        }
    }
}
