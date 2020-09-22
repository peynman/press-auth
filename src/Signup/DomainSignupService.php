<?php


namespace Larapress\Auth\Signup;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Larapress\Auth\Signin\ISigninService;
use Larapress\CRUD\BaseFlags;
use Larapress\CRUD\Events\CRUDCreated;
use Larapress\CRUD\Events\CRUDUpdated;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\Models\Role;
use Larapress\ECommerce\Services\Banking\IBankingService;
use Larapress\ECommerce\Services\SupportGroup\ISupportGroupService;
use Larapress\Notifications\CRUD\SMSMessageCRUDProvider;
use Larapress\Notifications\Models\SMSMessage;
use Larapress\Notifications\Services\SMSService\ISMSService;
use Larapress\Notifications\Services\SMSService\Jobs\SendSMS;
use Larapress\Profiles\CRUD\PhoneNumberCRUDProvider;
use Larapress\Profiles\CRUD\UserCRUDProvider;
use Larapress\Profiles\Flags\UserDomainFlags;
use Larapress\Profiles\Models\PhoneNumber;
use Larapress\Profiles\Repository\Domain\IDomainRepository;
use Larapress\Profiles\Services\FormEntry\IFormEntryService;
use Larapress\Profiles\IProfileUser;
use Larapress\Profiles\Models\FormEntry;

class DomainSignupService implements ISignupService
{

    public function signupUserWithData($dbPhone, $domaiId, $username, $password) {

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
        /** @var IDdomainRepository */
        $domainRepo = app()->make(IDomainRepository::class);
        $domain = $domainRepo->getCurrentRequestDomain();
        $dbPhone = PhoneNumber::query()
            ->where('number', $phone)
            ->where('domain_id', $domain->id)
            ->where('flags', '&', PhoneNumber::FLAGS_VERIFIED)
            ->first();

        // reject early, if no active domain is found
        if (is_null($domain)) {
            throw new Exception("Invalid domain");
        }

        // reject if there is no phone number record as verified
        if (is_null($dbPhone)) {
            throw new Exception(trans('auth.phone_expired'));
        }

        // reject if verification was more than 5 minutes ago
        $smsMessage = SMSMessage::find($msgId);
        if (is_null($dbPhone) || $smsMessage->data['mode'] !== 'verified' ) {
            throw new Exception(trans('auth.phone_expired'));
        }

        $user = null;
        DB::transaction(function () use ($dbPhone, $smsMessage, $domain, $username, $password, $request, &$user) {
            // update & create account
            $data = $smsMessage->data;
            $data['mode'] = 'registered';
            $smsMessage->update([
                'data' => $data
            ]);

            $userClass = config('larapress.crud.user.class');
            /** @var IProfileUser */
            $user = call_user_func([$userClass, 'create'], [
                'name' => $username,
                'password' => Hash::make($password)
            ]);
            $dbPhone->update([
                'user_id' => $user->id,
            ]);

            $role = Role::find(config('larapress.auth.signup.default-role'));
            $user->roles()->attach($role);
            $user->domains()->attach($domain, [
                'flags' => UserDomainFlags::REGISTRATION_DOMAIN | UserDomainFlags::MEMBERSHIP_DOMAIN,
            ]);

            $now = Carbon::now();
            CRUDCreated::dispatch($user, $user, UserCRUDProvider::class, $now);
            CRUDUpdated::dispatch($user, $dbPhone, PhoneNumberCRUDProvider::class, $now);
            SignupEvent::dispatch($user, $user, $domain, $request->ip(), time());
        });


        /** @var ISupportGroupService */
        $supportService = app(ISupportGroupService::class);
        // add user to support/introducer group, if we have introducer
        // add user gift balance too
        $supportService->updateUserRegistrationGiftWithIntroducer($request, $user, $request->getIntroducerID(), true, true);

        if (!is_null($request->get('campaign_id', null))) {
            $formId = $request->get('campaign_id');
            /** @var IFormEntryService */
            $formService = app(IFormEntryService::class);
            $formService->updateFormEntry(
                $request,
                $user,
                $formId,
            );
        }

        $user->updateUserCache();
        /** @var ISigninService */
        $signinService = app()->make(ISigninService::class);
        return $signinService->signinUser($dbPhone->number, $password);
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
        /** @var IDdomainRepository */
        $domainRepo = app()->make(IDomainRepository::class);
        $domain = $domainRepo->getCurrentRequestDomain();
        $dbPhone = PhoneNumber::query()
            ->with(['user'])
            ->where('number', $phone)
            ->where('domain_id', $domain->id)
            ->where('flags', '&', PhoneNumber::FLAGS_VERIFIED)
            ->first();

        // reject if there is no phone number record as verified
        if (is_null($dbPhone)) {
            throw new Exception(trans('auth.phone_expired'));
        }

        // reject if verification was more than 5 minutes ago
        $smsMessage = SMSMessage::find($msgId);
        if (is_null($dbPhone) || $smsMessage->data['mode'] !== 'verified') {
            throw new Exception(trans('auth.phone_expired'));
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
                $formId,
            );
        }

        /** @var ISigninService */
        $signinService = app()->make(ISigninService::class);
        return $signinService->signinUser($dbPhone->user->name, $password);
    }

    /**
     * Undocumented function
     *
     * @param String $phone
     * @return bool
     */
    public function sendPhoneVerifySMS(string $phone)
    {
        /** @var ISMSService */
        $smsService = app()->make(ISMSService::class);
        /** @var IDdomainRepository */
        $domainRepo = app()->make(IDomainRepository::class);
        $domain = $domainRepo->getCurrentRequestDomain();
        $gateway = $smsService->findGatewayData($domain);

        if (is_null($gateway)) {
            throw new Exception(trans('larapress::auth.exceptions.no_gateway'));
        }

        if (config('larapress.auth.signup.sms.numbers_only', true)) {
            $verify_code = Helpers::randomNumbers(config('larapress.auth.signup.sms.code_len', 5));
        } else {
            $verify_code = Helpers::randomString(config('larapress.auth.signup.sms.code_len', 5));
        }
        $message = $verify_code; //trans('larapress::auth.signup.sms.verify', ['code' => );

        $smsMessage = SMSMessage::create([
            'author_id' => config('larapress.auth.signup.sms.default-author'),
            'sms_gateway_id' => $gateway->id,
            'from' => config('larapress.auth.signup.sms.from'),
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

        /** @var IDomainRepository */
        $domainRepo = app()->make(IDomainRepository::class);
        $currDomain = $domainRepo->getCurrentRequestDomain();

        /** @var IDomainRepository */
        $domainRepo = app()->make(IDomainRepository::class);
        $currDomain = $domainRepo->getCurrentRequestDomain();
        $dbPhone = PhoneNumber::where('number', $phone)
            ->where('domain_id', $currDomain->id)
            ->first();

        $now = Carbon::now();
        if (is_null($dbPhone)) {
            $dbPhone = PhoneNumber::create([
                'number' => $phone,
                'user_id' => null,
                'domain_id' => $currDomain->id,
                'flags' => 0,
            ]);
            CRUDCreated::dispatch($dbPhone, PhoneNumberCRUDProvider::class, $now);
        }
        CRUDCreated::dispatch($smsMessage, SMSMessageCRUDProvider::class, $now);
        SendSMS::dispatch($smsMessage);

        return [
            'message' => trans('larapress::auth.signup.messages.code_sent'),
        ];
    }

    /**
     * Undocumented function
     *
     * @param String $phone
     * @param String $code
     * @return bool
     */
    public function verifyPhoneSMS(string $phone, string $code)
    {
        $smsMessage = SMSMessage::query()
            ->where('from', config('larapress.auth.signup.sms.from'))
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
        }

        return [
            'message' => $isValid ? trans('larapress::auth.signup.messages.verify_success') : trans('larapress::auth.signup.messages.verify_failed'),
            'status' => $isValid,
            'msg_id' => $smsMessage->id,
        ];
    }


    /**
     * Undocumented function
     *
     * @param String $phone
     * @param String $code
     * @return array
     */
    public function resolveSignUpWithPhoneVerifySMS(string $phone, string $code)
    {
        $valid = $this->verifyPhoneSMS($phone, $code);
        if ($valid['status']) {
            /** @var IDomainRepository */
            $domainRepo = app()->make(IDomainRepository::class);
            $currDomain = $domainRepo->getCurrentRequestDomain();
            $dbPhone = PhoneNumber::with('user')
                ->where('number', $phone)
                ->where('domain_id', $currDomain->id)
                ->first();
            if (is_null($dbPhone) || is_null($dbPhone->user)) { // phone number does not exists in our database in current domain
                if (is_null($dbPhone)) {
                    $dbPhone = PhoneNumber::create([
                        'number' => $phone,
                        'user_id' => null,
                        'domain_id' => $currDomain->id,
                        'flags' => PhoneNumber::FLAGS_VERIFIED,
                    ]);
                } else if (!BaseFlags::isActive($dbPhone->flags, PhoneNumber::FLAGS_VERIFIED)) {
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

        throw new AppException(AppException::ERR_INVALID_PARAMS);
    }

    /**
     * Undocumented function
     *
     * @param String $email
     * @return void
     */
    public function sendEmailVerify(string $email)
    {
    }

    /**
     * Undocumented function
     *
     * @param String $email
     * @param String $code
     * @return void
     */
    public function verifyEmail(string $email, string $code)
    {
    }
}
