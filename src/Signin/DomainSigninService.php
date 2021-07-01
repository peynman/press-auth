<?php

namespace Larapress\Auth\Signin;

use Carbon\Carbon;
use Exception;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Larapress\CRUD\BaseFlags;
use Larapress\Profiles\Flags\UserFlags;
use Larapress\Profiles\IProfileUser;
use Larapress\Profiles\Repository\Domain\IDomainRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\StatefulGuard;
use Larapress\CRUD\Exceptions\RequestException;

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
        if (is_object($domain)) { $domain = $domain->id; }

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
                throw new RequestException(trans('larapress::auth.exceptions.invalid_credentials'));
            }
        }
        $user =  Auth::user();

        if (!is_null($user)) {
            if (BaseFlags::isActive($user->flags, UserFlags::BANNED)) {
                throw new RequestException(trans('larapress::auth.exceptions.banned'));
            }

            SigninEvent::dispatch($user, $domain, $request->ip(), Carbon::now());
        }

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
     * @param IProfileUser|Model $user
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
            throw new RequestException(trans('larapress::auth.exceptions.invalid_password'));
        }
    }
}
