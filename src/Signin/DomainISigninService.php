<?php

namespace Larapress\Auth\Signin;

use Exception;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\Profiles\IProfileUser;
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
            $token = $guard->attempt($request->getCredentials(), $request->get('remember', false));
            if ($token !== false) {
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
        }
        if ($success) {
            $user =  Auth::user();
            event(new SigninEvent(
                $user,
                $this->domainRepository->getRequestDomain($request),
                $request->getClientIp()
            ));

            return [
                'tokens' => $guards,
                'user' => $user,
                'message' => trans('larapress::auth.signin.success')
            ];
        }

        throw new AppException (AppException::ERR_INVALID_CREDENTIALS );
    }


    /**
     * @return \Larapress\Auth\Signin\SigninResponse
     * @throws \Larapress\Core\Exceptions\AppException
     */
    public function signinUser(Authenticatable $user) {
        $guards = config('auth.guards');
        $request = Request::createFromGlobals();
        foreach ($guards as $guardName => $guardParams) {
            /** @var \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard $guard */
            $guard = Auth::guard($guardName);
            $token = $guard->login($user);
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
            }
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
            } catch (Exception $e) {}
        }

        return [
            'success' => true,
            'message' => trans(config('larapress.auth.theme.translations.namespace').'::larapress.auth..logout.success')
        ];
    }
}
