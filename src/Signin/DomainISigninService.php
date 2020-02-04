<?php

namespace Larapress\Auth\Signin;

use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;
use Larapress\Core\Exceptions\AppException;
use Larapress\Profiles\Models\Domain;
use Larapress\Profiles\Repository\Domain\IDomainRepository;

class DomainISigninService implements ISigninService
{
    /**
     * @var \Larapress\Profiles\Repository\Domain\IDomainRepository
     */
    private $domainRepository;

    /**
     * DomainISigninService constructor.
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
        $guards = config('auth.guards');
        $success = false;

        foreach ($guards as $guardName => $guardParams) {
            /** @var \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard $guard */
            $guard = Auth::guard($guardName);
            $token = $guard->attempt($request->getCredentials(), true);
            if ($guard instanceof SessionGuard) {
                $token = [
                    'remember' => $guard->getRecallerName(),
                    'session' => $request->getSession()->getName(),
                ];
            }

            if (!is_null($token)) {
                $guards[$guardName] = $token;
                $success = true;
            }
        }
        if ($success) {
            $user =  Auth::user();
            event(new SigninEvent(
                $user,
                $this->domainRepository->getRequestDomain($request),
                $request->getClientIp()
            ));

            return [
                'success' => $success,
                'tokens' => $guards,
                'user' => $user,
                'message' => trans(config('larapress.auth.theme.translations.namespace').'::larapress.auth.signin.success')
            ];
        }

        throw new AppException(AppException::ERR_INVALID_CREDENTIALS );
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
            $guard->logout();
        }

        return [
            'success' => true,
            'message' => trans(config('larapress.auth.theme.translations.namespace').'::larapress.auth..logout.success')
        ];
    }
}