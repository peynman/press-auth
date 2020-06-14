<?php


namespace Larapress\Auth\Signup;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Larapress\Auth\Signin\ISigninService;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\Models\Role;
use Larapress\Notifications\Models\SMSMessage;
use Larapress\Notifications\SMSService\ISMSService;
use Larapress\Notifications\SMSService\Jobs\SendSMS;
use Larapress\Profiles\Flags\UserDomainFlags;
use Larapress\Profiles\Models\PhoneNumber;
use Larapress\Profiles\Repository\Domain\IDomainRepository;

class DomainSignupService implements ISignupService
{

    /**
     * @param String $phone
     * @param String $username
     * @param String $password
     * @return array
     */
    public function signupWithPhoneNumber(string $phone, string $msgId, string $username, string $password)
    {
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
            throw new Exception("Number is not verified");
        }

        // reject if phone number has already a user
        if (!is_null($dbPhone->user_id)) {
            throw new Exception("Number is not available, reset password");
        }

        // reject if verification was more than 5 minutes ago
        $smsMessage = SMSMessage::find($msgId);
        if (is_null($dbPhone) || $smsMessage->data['mode'] !== 'verified' || Carbon::now()->diffInMinutes($smsMessage->updated_at) > 5) {
            throw new Exception("Number is not verified");
        }

        return DB::transaction(function () use ($dbPhone, $smsMessage, $domain, $username, $password) {
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
            $user->roles()->attach(Role::find('larapress.auth.signup.default-role'));
            $user->domains()->attach($domain, [
                'flags' => UserDomainFlags::REGISTRATION_DOMAIN | UserDomainFlags::MEMBERSHIP_DOMAIN,
            ]);

            /** @var ISigninService */
            $signinService = app()->make(ISigninService::class);
            return $signinService->signinUser($user);
        });

    }

    /**
     * Undocumented function
     *
     * @param [type] $phone
     * @param [type] $msgId
     * @param [type] $password
     * @return void
     */
    public function resetWithPhoneNumber(string $phone, string $msgId, string $password)
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
            throw new Exception("Number is not verified");
        }

        // reject if verification was more than 5 minutes ago
        $smsMessage = SMSMessage::find($msgId);
        if (is_null($dbPhone) || $smsMessage->data['mode'] !== 'verified' || Carbon::now()->diffInMinutes($smsMessage->updated_at) > 5) {
            throw new Exception("Number is not verified");
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

        /** @var ISigninService */
        $signinService = app()->make(ISigninService::class);
        return $signinService->signinUser($dbPhone->user);
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

        if (config('larapress.auth.sms.numbers_only')) {
            $verify_code = Helpers::randomNumbers(config('larapress.auth.signup.sms.code_len', 5));
        } else {
            $verify_code = Helpers::randomString(config('larapress.auth.signup.sms.code_len', 5));
        }
        $message = trans('larapress::auth.signup.sms.verify', ['code' => $verify_code]);

        $smsMessage = SMSMessage::create([
            'author_id' => config('larapress.auth.signup.sms.default-author'),
            'sms_gateway_id' => $gateway->id,
            'from' => config('larapress.auth.signup.sms.from'),
            'to' => $phone,
            'message' => $message,
            'flags' => SMSMessage::FLAGS_VERIFICATION_MESSAGE,
            'data' => [
                'mode' => 'verify',
                'code' => $verify_code,
                'domain' => $domain,
            ]
        ]);
        // event(new SendSMS($smsMessage));

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
