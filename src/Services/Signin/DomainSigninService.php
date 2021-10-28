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
use Larapress\Auth\Services\Signin\Requests\OTCRequest;
use Larapress\Auth\Services\Signin\Requests\SigninRequest;
use Larapress\Auth\Services\Signup\Requests\OTCCheckRequest;
use Larapress\CRUD\Events\CRUDCreated;
use Larapress\CRUD\Events\CRUDUpdated;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Exceptions\RequestException;
use Larapress\CRUD\Extend\Helpers;
use Larapress\Notifications\CRUD\SMSMessageCRUDProvider;
use Larapress\Notifications\Models\SMSGatewayData;
use Larapress\Notifications\Models\SMSMessage;
use Larapress\Notifications\Services\SMSService\Jobs\SendSMS;
use Larapress\Profiles\Services\ProfileUser\IProfileUserServices;
use Mews\Captcha\Facades\Captcha;
use Tymon\JWTAuth\Facades\JWTAuth;
use Larapress\Profiles\IProfileUser;
use Larapress\Profiles\Models\PhoneNumber;

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
     * @param SigninRequest $request
     *
     * @return array
     *
     * @throws Exception
     */
    public function signin(SigninRequest $request)
    {
        return $this->signinCredentials(
            $this->domainRepository->getRequestDomain($request),
            $request->getUsername(),
            $request->getPassword()
        );
    }

    /**
     * Undocumented function
     *
     * @param string $phone
     * @return array
     */
    public function sendSigninOTC($phone)
    {
        $domain = $this->domainRepository->getCurrentRequestDomain();

        $gateway = SMSGatewayData::find(config('larapress.auth.signup.sms.default_gateway'));

        if (is_null($gateway)) {
            throw new AppException(AppException::ERR_OBJECT_NOT_FOUND, trans('larapress::auth.exceptions.no_gateway'));
        }

        if (config('larapress.auth.signup.sms.numbers_only', true)) {
            $verify_code = Helpers::randomNumbers(config('larapress.auth.signup.sms.code_len', 5));
        } else {
            $verify_code = Helpers::randomString(config('larapress.auth.signup.sms.code_len', 5));
        }
        $message = trans('larapress::auth.signin.otc_code', ['code' => $verify_code]);

        $now = Carbon::now();
        $dbPhone = PhoneNumber::where('number', $phone)
            ->where('domain_id', $domain->id)
            ->first();

        if (is_null($dbPhone)) {
            throw new RequestException(trans('larapress::auth.signin.otc_not_found'));
        }

        $smsMessage = SMSMessage::create([
            'author_id' => config('larapress.auth.signup.sms.default_author'),
            'sms_gateway_id' => $gateway->id,
            'from' => trans('larapress::auth.signup.messages.from'),
            'to' => $phone,
            'message' => $message,
            'flags' => SMSMessage::FLAGS_VERIFICATION_MESSAGE,
            'status' => SMSMessage::STATUS_CREATED,
            'data' => [
                'mode' => 'otc',
                'code' => $verify_code,
                'domain' => $domain,
            ]
        ]);
        CRUDCreated::dispatch(null, $smsMessage, SMSMessageCRUDProvider::class, $now);
        SendSMS::dispatch($smsMessage);

        return [
            'message' => trans('larapress::auth.signup.messages.code_sent'),
        ];
    }

    /**
     * Undocumented function
     *
     * @param string $phone
     * @param string $code
     * @return array
     */
    public function signinWithOTC($phone, $code)
    {
        $domain = $this->domainRepository->getCurrentRequestDomain();
        /** @var PhoneNumber */
        $dbPhone = PhoneNumber::where('number', $phone)
            ->where('domain_id', $domain->id)
            ->first();

        if (is_null($dbPhone)) {
            throw new RequestException(trans('larapress::auth.signin.otc_not_found'));
        }

        $smsMessage = SMSMessage::query()
            ->where('from', trans('larapress::auth.signup.messages.from'))
            ->where('to', $phone)
            ->where('flags', '&', SMSMessage::FLAGS_VERIFICATION_MESSAGE)
            ->orderBy('created_at', 'DESC')
            ->first();

        $isValid = !is_null($smsMessage) &&
            isset($smsMessage->data['code']) &&
            isset($smsMessage->data['mode']) &&
            $smsMessage->data['mode'] === 'otc' &&
            $smsMessage->data['code'] === $code;

        if ($isValid) {
            $data = $smsMessage->data;
            $data['mode'] = 'verified';
            $smsMessage->update([
                'data' => $data
            ]);
            CRUDUpdated::dispatch(null, $smsMessage, SMSMessageCRUDProvider::class, Carbon::now());

            return $this->signinUser(
                $dbPhone->user
            );
        }

        throw new RequestException(trans('larapress::auth.signin.otc_failed'));
    }

    /**
     * @return \Larapress\Auth\Signin\SigninResponse
     *
     * @throws Exception
     */
    public function signinCredentials($domain, string $username, string $password)
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
     * Undocumented function
     *
     * @param IProfileUser $userId
     * @return array
     */
    public function signinUser(IProfileUser $user)
    {
        $tokens = [];
        $guards = config('auth.guards');
        $request = Request::createFromGlobals();
        foreach ($guards as $guardName => $guardParams) {
            /** @var StatefulGuard $guard */
            $guard = Auth::guard($guardName);
            $token = $guard->login($user, true);
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
            $user->getMembershipDomainId(),
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
