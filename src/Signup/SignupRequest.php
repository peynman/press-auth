<?php

namespace Larapress\Auth\Signup;

use Illuminate\Foundation\Http\FormRequest;
use Larapress\Profiles\Models\Form;
use Larapress\Profiles\Services\FormEntry\IFormEntryService;

/**
 * @bodyParam email string required_without:phone Email to use in registration form (should be verified already).
 * @bodyParam phone string required_without:email Phone number to use in registration form (should be verified already).
 * @bodyParam username string required Registration username.
 * @bodyParam password string required Registration password.
 * @bodyParam msg_id int required Verification message id (returned by phone/email verification endpoints).
 * @bodyParam introducer_id int User id to attach as introducer.
 * @bodyParam campaign_id int Form id to attach as registration campain.
 */
class SignupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $formValidations = [];
        if (!is_null(config('larapress.auth.signup.autofill_form'))) {
            $formId = config('larapress.auth.signup.autofill_form');
            $form = Form::find($formId);
            if (!is_null($form)) {
                /** @var IFormEntryService */
                $formService = app(IFormEntryService::class);
                [$rules, $inputs] = $formService->getFormValidationRules($form);
                foreach ($rules as $ruleName => $rule) {
                    $formValidations[$form->name.'.'.$ruleName] = $rule;
                }
            }
        }

        $rules = array_merge([
            'email' => 'required_without:phone|email|exists:emails,email',
            'phone' => 'required_without:email|numeric|exists:phone_numbers,number',
            'username' => 'required|string|min:6|max:255|unique:users,name|regex:/(^([a-zA-Z0-9\_\-\.]+)(\d+)?$)/u',
            'password' => 'required|string|min:6|confirmed',
            'msgId' => 'required|numeric|exists:sms_messages,id',
            'introducerId' => 'nullable|numeric|exists:users,id',
            'campaignId' => 'nullable|numeric|exits:forms,id',
        ], $formValidations);

        if (!is_null(config('larapress.auth.signup.sms.phone_digits'))) {
            $rules['phone'] .= '|digits:'.config('larapress.auth.signup.sms.phone_digits');
        }

        return $rules;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getPhone()
    {
        return $this->get('phone');
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->get('email');
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getUsername()
    {
        return $this->get('username');
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getPassword()
    {
        return $this->get('password');
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getMessageID()
    {
        return $this->get('msgId');
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getIntroducerID()
    {
        return $this->get('introducerId');
    }
}
