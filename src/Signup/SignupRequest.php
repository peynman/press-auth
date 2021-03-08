<?php

namespace Larapress\Auth\Signup;

use Illuminate\Foundation\Http\FormRequest;
use Larapress\Profiles\Models\Form;
use Larapress\Profiles\Services\FormEntry\IFormEntryService;

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

        return array_merge([
            'phone' => 'required|numeric|exists:phone_numbers,number|digits:11',
            'username' => 'required|string|min:6|max:255|unique:users,name|regex:/(^([a-zA-Z0-9\_\-]+)(\d+)?$)/u',
            'password' => 'string|min:6|confirmed|required',
            'msg_id' => 'required|numeric|exists:sms_messages,id',
            'introducer_id' => 'nullable|numeric|exists:users,id',
        ], $formValidations);
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
        return $this->get('msg_id');
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getIntroducerID()
    {
        return $this->get('introducer_id');
    }
}
