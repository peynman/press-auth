<?php

namespace Larapress\Auth\Services\Signin;

use Carbon\Carbon;
use Exception;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\BaseFlags;
use Larapress\Profiles\Flags\UserFlags;
use Larapress\Profiles\Repository\Domain\IDomainRepository;
use Illuminate\Contracts\Auth\StatefulGuard;
use Larapress\CRUD\Exceptions\RequestException;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use Larapress\Profiles\Services\ProfileUser\IProfileUserServices;
use Larapress\Profiles\Services\ProfileUser\ProfileUserQueryRequest;
use Mews\Captcha\Facades\Captcha;
use Tymon\JWTAuth\Facades\JWTAuth;
use Larapress\Profiles\IProfileUser;

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
     *
     */
    public function __construct(IDomainRepository $domainRepository)
    {
        $this->domainRepository = $domainRepository;
    }

    /**
     * @param \Larapress\Auth\Signin\SigninRequest $request
     *
     * @return array
     *
     * @throws Exception
     */
    public function signin(SigninRequest $request)
    {
        return $this->signinUser(
            $this->domainRepository->getRequestDomain($request),
            $request->getUsername(),
            $request->getPassword()
        );
    }

    /**
     * @return \Larapress\Auth\Signin\SigninResponse
     *
     * @throws Exception
     */
    public function signinUser($domain, string $username, string $password)
    {
        if (is_object($domain)) {
            $domain = $domain->id;
        }

        $tokens = [];
        $guards = config('auth.guards');
        $request = Request::createFromGlobals();
        foreach ($guards as $guardName => $guardParams) {
            /** @var StatefulGuard $guard */
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
                    $tokens[$guardName] = $token;
                }
            } else {
                throw new RequestException(trans('larapress::auth.exceptions.invalid_credentials'), 400, [
                    'captcha' => Captcha::create('default', true),
                ]);
            }
        }

        /** @var IProfileUser */
        $user =  Auth::user();
        if (BaseFlags::isActive($user->flags, UserFlags::BANNED)) {
            throw new RequestException(trans('larapress::auth.exceptions.banned'), 400, [
                'captcha' => Captcha::create('default', true),
            ]);
        }

        SigninEvent::dispatch(
            $user,
            $domain,
            $request->ip(),
            $request->userAgent(),
            $request->get('client', 'web'),
            Carbon::now()
        );

        /** @var IProfileUserServices */
        $service = app(IProfileUserServices::class);
        $user = $service->userDetails($user);

        return [
            'tokens' => $tokens,
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
            /** @var StatefulGuard $guard */
            $guard = Auth::guard($guardName);
            try {
                $guard->logout();
            } catch (Exception $e) {
            }
        }

        return [
            'success' => true,
            'message' => trans('larapress::auth.logout.success')
        ];
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function refreshToken()
    {
        return [
            'tokens' => [
                'api' => JWTAuth::refresh(JWTAuth::getToken()),
            ],
        ];
    }
}
