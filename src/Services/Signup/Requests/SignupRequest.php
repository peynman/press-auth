<?php

namespace Larapress\Auth\Services\Signup\Requests;

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
    /** @var Form */
    protected $form;

    /** @var Form */
    protected $campaignForm;
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
            if (is_numeric($formId)) {
                $this->form = Form::find($formId);
            } else {
                $this->form = Form::query()->where('name', $formId)->first();
            }
            if (!is_null($this->form)) {
                /** @var IFormEntryService */
                $formService = app(IFormEntryService::class);
                $rules = $formService->getFormValidationRules($this->form);
                foreach ($rules as $ruleName => $rule) {
                    $formValidations[$this->form->name . '.' . $ruleName] = $rule;
                }
            }
        }

        $campaignId = $this->get('campaignId');
        if (!is_null($campaignId)) {
            if (is_numeric($campaignId)) {
                $this->campaignForm = Form::find($campaignId);
            } else {
                $this->campaignForm = Form::query()->where('name', $campaignId)->first();
            }

            if (!is_null($this->campaignForm)) {
                /** @var IFormEntryService */
                $formService = app(IFormEntryService::class);
                $rules = $formService->getFormValidationRules($this->campaignForm);
                foreach ($rules as $ruleName => $rule) {
                    $formValidations[$this->campaignForm->name . '.' . $ruleName] = $rule;
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
            $rules['phone'] .= '|digits:' . config('larapress.auth.signup.sms.phone_digits');
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

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getFormData()
    {
        if (!is_null($this->getForm())) {
            return $this->get($this->getForm()->name, []);
        }

        return [];
    }

    /**
     * Undocumented function
     *
     * @return Form|null
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Undocumented function
     *
     * @return Form|null
     */
    public function getCampaignForm()
    {
        return $this->campaignForm;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getCampaignFormData()
    {
        if (!$this->getCampaignForm()) {
            return $this->get($this->getCampaignForm()->name, []);
        }

        return [];
    }
}
