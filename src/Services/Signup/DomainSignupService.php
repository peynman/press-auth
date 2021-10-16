<?php


namespace Larapress\Auth\Services\Signup;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Larapress\Auth\Services\Signup\Requests\SignupRequest;
use Larapress\Auth\Signin\ISigninService;
use Larapress\CRUD\BaseFlags;
use Larapress\CRUD\Events\CRUDCreated;
use Larapress\CRUD\Events\CRUDUpdated;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Exceptions\RequestException;
use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\Models\Role;
use Larapress\Notifications\CRUD\SMSMessageCRUDProvider;
use Larapress\Notifications\Models\SMSGatewayData;
use Larapress\Notifications\Models\SMSMessage;
use Larapress\Profiles\CRUD\PhoneNumberCRUDProvider;
use Larapress\Profiles\CRUD\UserCRUDProvider;
use Larapress\Profiles\Flags\UserDomainFlags;
use Larapress\Profiles\Models\PhoneNumber;
use Larapress\Profiles\Repository\Domain\IDomainRepository;
use Larapress\Profiles\Services\FormEntry\IFormEntryService;
use Larapress\Profiles\IProfileUser;
use Larapress\Profiles\Models\Form;
use Laravel\Socialite\Facades\Socialite;

class DomainSignupService implements ISignupService
{
    /** @bar IDomainRepository */
    protected $domainRepo;
    /** @var ISigninService */
    protected $signinService;
    public function __construct(IDomainRepository $domainRepo, ISigninService $signinService)
    {
        $this->domainRepo = $domainRepo;
        $this->signinService = $signinService;
    }

    /**
     * Undocumented function
     *
     * @param Domain $domain
     * @param string $username
     * @param string $password
     * @return IProfileUser
     */
    public function signupUserWithData($domain, $username, $password)
    {
        $userClass = config('larapress.crud.user.model');
        /** @var IProfileUser */
        $user = call_user_func([$userClass, 'create'], [
            'name' => $username,
            'password' => Hash::make($password)
        ]);
        $role = Role::find(config('larapress.auth.signup.default_role'));
        if (is_null($role)) {
            throw new AppException(AppException::ERR_OBJECT_NOT_FOUND, trans('larapress::auth.exceptions.no_customer_role'));
        }

        $user->roles()->attach($role);
        $user->domains()->attach($domain, [
            'flags' => UserDomainFlags::REGISTRATION_DOMAIN | UserDomainFlags::MEMBERSHIP_DOMAIN,
        ]);

        return $user;
    }

    /**
     * Undocumented function
     *
     * @param IProfileUser $user
     * @param SignupRequest $request
     *
     * @return void
     */
    public function fillSignupFormsForRegistrationRequest(IProfileUser $user, SignupRequest $request)
    {
        /** @var IFormEntryService */
        $formService = app(IFormEntryService::class);
        if (!is_null($request->get('campaign_id', null))) {
            $formId = $request->get('campaign_id');
            $formService->updateFormEntry(
                $request,
                $user,
                $formId
            );
        }
        if (!is_null(config('larapress.auth.signup.autofill_form'))) {
            $formId = config('larapress.auth.signup.autofill_form');
            $form = Form::find($formId);
            if (!is_null($form)) {
                $values = $request->get($form->name);
                $formRequest = clone $request;
                $formRequest->merge($values);
                $formService->updateFormEntry(
                    $formRequest,
                    $user,
                    $formId
                );
            }
        }
    }

    /**
     * @param SignupRequest $request
     * @return array
     */
    public function signupWithPhoneNumber(SignupRequest $request)
    {
        $phone = $request->getPhone();
        $username = $request->getUsername();
        $password = $request->getPassword();
        $msgId = $request->getMessageID();
        $domain = $this->domainRepo->getRequestDomain($request);

        $dbPhone = PhoneNumber::query()
            ->where('number', $phone)
            ->where('domain_id', $domain->id)
            ->where('flags', '&', PhoneNumber::FLAGS_VERIFIED)
            ->first();

        // reject early, if no active domain is found
        if (is_null($domain)) {
            throw new RequestException("Invalid domain");
        }

        // reject if there is no phone number record as verified
        if (is_null($dbPhone)) {
            throw new RequestException(trans('larapress::auth.exceptions.phone_expired'));
        }

        // reject if verification was more than 5 minutes ago
        $smsMessage = SMSMessage::find($msgId);
        if (is_null($dbPhone) || $smsMessage->data['mode'] !== 'verified') {
            throw new RequestException(trans('larapress::auth.exceptions.phone_expired'));
        }

        $user = null;
        DB::transaction(function () use ($dbPhone, $smsMessage, $domain, $username, $password, $request, &$user) {
            // update & create account
            $data = $smsMessage->data;
            $data['mode'] = 'registered';
            $smsMessage->update([
                'data' => $data
            ]);

            $user = $this->signupUserWithData(
                $domain,
                $username,
                $password
            );
            $dbPhone->update([
                'user_id' => $user->id,
            ]);
            $this->fillSignupFormsForRegistrationRequest($user, $request);

            $now = Carbon::now();
            CRUDCreated::dispatch($user, $user, UserCRUDProvider::class, $now);
            CRUDUpdated::dispatch($user, $dbPhone, PhoneNumberCRUDProvider::class, $now);
            SignupEvent::dispatch($user, $domain, $request->getIntroducerID(), $request->ip(), $now);
        });

        return $this->signinService->signinUser($domain, $dbPhone->number, $password);
    }

    /**
     * Undocumented function
     *
     * @param [type] $phone
     * @param [type] $msgId
     * @param [type] $password
     * @return void
     */
    public function resetWithPhoneNumber(Request $request, string $phone, string $msgId, string $password)
    {
        $domain = $this->domainRepo->getRequestDomain($request);
        $dbPhone = PhoneNumber::query()
            ->with(['user'])
            ->where('number', $phone)
            ->where('domain_id', $domain->id)
            ->where('flags', '&', PhoneNumber::FLAGS_VERIFIED)
            ->first();

        // reject if there is no phone number record as verified
        if (is_null($dbPhone)) {
            throw new RequestException(trans('larapress::auth.exceptions.phone_expired'));
        }

        // reject if verification was more than 5 minutes ago
        $smsMessage = SMSMessage::find($msgId);
        if (is_null($dbPhone) || $smsMessage->data['mode'] !== 'verified') {
            throw new RequestException(trans('larapress::auth.exceptions.phone_expired'));
        }

        // update & reset password
        $data = $smsMessage->data;
        $data['mode'] = 'reseted';
        $smsMessage->update([
            'data' => $data
        ]);

        $dbPhone->user->update([
            'password' => Hash::make($password)
        ]);

        if (!is_null($request->get('campaign_id', null))) {
            $formId = $request->get('campaign_id');
            /** @var IFormEntryService */
            $formService = app(IFormEntryService::class);
            $formService->updateFormEntry(
                $request,
                $dbPhone->user,
                $formId
            );
        }

        return $this->signinService->signinUser($domain, $dbPhone->user->name, $password);
    }

    /**
     * Undocumented function
     *
     * @param String $phone
     * @param Domain|string $domain
     *
     * @return array
     */
    public function sendPhoneVerifySMS(string $phone, $domain)
    {
        $domain = $this->domainRepo->getCurrentRequestDomain();

        $gateway = SMSGatewayData::find(config('larapress.auth.signup.sms.default_gateway'));

        if (is_null($gateway)) {
            throw new AppException(AppException::ERR_OBJECT_NOT_FOUND, trans('larapress::auth.exceptions.no_gateway'));
        }

        if (config('larapress.auth.signup.sms.numbers_only', true)) {
            $verify_code = Helpers::randomNumbers(config('larapress.auth.signup.sms.code_len', 5));
        } else {
            $verify_code = Helpers::randomString(config('larapress.auth.signup.sms.code_len', 5));
        }
        $message = trans('larapress::auth.signup.messages.signup_code', ['code' => $verify_code]);

        $smsMessage = SMSMessage::create([
            'author_id' => config('larapress.auth.signup.sms.default_author'),
            'sms_gateway_id' => $gateway->id,
            'from' => trans('larapress::auth.signup.messages.from'),
            'to' => $phone,
            'message' => $message,
            'flags' => SMSMessage::FLAGS_VERIFICATION_MESSAGE,
            'status' => SMSMessage::STATUS_CREATED,
            'data' => [
                'mode' => 'verify',
                'code' => $verify_code,
                'domain' => $domain,
            ]
        ]);

        $now = Carbon::now();
        $dbPhone = PhoneNumber::where('number', $phone)
            ->where('domain_id', $domain->id)
            ->first();

        if (is_null($dbPhone)) {
            $dbPhone = PhoneNumber::create([
                'number' => $phone,
                'user_id' => null,
                'domain_id' => $domain->id,
                'flags' => 0,
            ]);
            CRUDCreated::dispatch(null, $dbPhone, PhoneNumberCRUDProvider::class, $now);
        }
        CRUDCreated::dispatch(null, $smsMessage, SMSMessageCRUDProvider::class, $now);
        // SendSMS::dispatch($smsMessage);

        return [
            'message' => trans('larapress::auth.signup.messages.code_sent'),
        ];
    }

    /**
     * Undocumented function
     *
     * @param String $phone
     * @param String $code
     *
     * @return array
     */
    public function verifyPhoneSMS(string $phone, string $code)
    {
        $smsMessage = SMSMessage::query()
            ->where('from', trans('larapress::auth.signup.messages.from'))
            ->where('to', $phone)
            ->where('flags', '&', SMSMessage::FLAGS_VERIFICATION_MESSAGE)
            ->orderBy('created_at', 'DESC')
            ->first();

        $isValid = !is_null($smsMessage) &&
            isset($smsMessage->data['code']) &&
            isset($smsMessage->data['mode']) &&
            $smsMessage->data['mode'] === 'verify' &&
            $smsMessage->data['code'] === $code;

        if ($isValid) {
            $data = $smsMessage->data;
            $data['mode'] = 'verified';
            $smsMessage->update([
                'data' => $data
            ]);
            CRUDUpdated::dispatch(null, $smsMessage, SMSMessageCRUDProvider::class, Carbon::now());

            return [
                'message' => $isValid ?
                    trans('larapress::auth.signup.messages.verify_success') :
                    trans('larapress::auth.signup.messages.verify_failed'),
                'status' => $isValid,
                'msgId' => $smsMessage->id,
            ];
        }

        throw new RequestException(trans('larapress::auth.signup.messages.verify_failed'));
    }

    /**
     * Undocumented function
     *
     * @param String $phone
     * @param String $code
     * @param Domain|int $domain
     *
     * @return array
     */
    public function resolveSignUpWithPhoneVerifySMS(string $phone, string $code, $domain)
    {
        if (is_object($domain)) { $domain = $domain->id; }

        $valid = $this->verifyPhoneSMS($phone, $code, $domain);
        if ($valid['status']) {
            $dbPhone = PhoneNumber::with('user')
                ->where('number', $phone)
                ->where('domain_id', $domain)
                ->first();
            if (is_null($dbPhone) || is_null($dbPhone->user)) { // phone number does not exists in our database in current domain
                if (is_null($dbPhone)) {
                    $dbPhone = PhoneNumber::create([
                        'number' => $phone,
                        'user_id' => null,
                        'domain_id' => $domain,
                        'flags' => PhoneNumber::FLAGS_VERIFIED,
                    ]);
                } elseif (!BaseFlags::isActive($dbPhone->flags, PhoneNumber::FLAGS_VERIFIED)) {
                    $dbPhone->update([
                        'flags' => PhoneNumber::FLAGS_VERIFIED,
                    ]);
                    CRUDUpdated::dispatch(null, $dbPhone, PhoneNumberCRUDProvider::class, Carbon::now());
                }

                return $valid;
            } else {
                $valid['reset'] = true; // ask for reset password
                $valid['message'] = trans('larapress::auth.signup.messages.already_exist');
                return $valid;
            }
        }

        throw new RequestException(trans('larapress::auth.signup.messages.verify_failed'));
    }

    /**
     * Undocumented function
     *
     * @param String $email
     * @param Domain|string $domain
     *
     * @return array
     */
    public function sendEmailVerify(string $email, $domain)
    {
    }

    /**
     * Undocumented function
     *
     * @param String $email
     * @param String $code
     * @param Domain|string $domain
     *
     * @return array
     */
    public function verifyEmail(string $email, string $code)
    {
    }

    /**
     * Undocumented function
     *
     * @param string $driver

     * @return void
     */
    public function verifySocialite(string $driver)
    {
        $drivers = array_keys(config('larapress.auth.signup.socialite'));
        if (in_array($driver, $drivers)) {
            $d = Socialite::driver($driver);
            if (isset($drivers[$driver]['scopes'])) {
                $d->setScopes($drivers[$driver]['scopes']);
            }

            return $d->redirect();
        } else {
            throw new AppException(AppException::ERR_INVALID_SIGNUP_DRIVER);
        }
    }

    public function signupWithSocialiteDriver(string $driver)
    {
        $drivers = array_keys(config('larapress.auth.signup.socialite'));
        if (in_array($driver, $drivers)) {
            $user = Socialite::driver($driver)->user();
        } else {
            throw new AppException(AppException::ERR_INVALID_SIGNUP_DRIVER);
        }
    }
}
