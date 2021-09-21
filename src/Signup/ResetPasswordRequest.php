<?php

namespace Larapress\Auth\Signup;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Reset password request, this request is available after VerifyPhoneCheckRequest
 *
 * @queryParam phone required the phone number (account) to reset password for
 * @queryParam password the new password
 * @queryParam msg_id the verification sms id which is received from VerifyPhoneCheckRequest end point
 *
 */
class ResetPasswordRequest extends FormRequest
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
        return [
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|numeric|regex:/(09)[0-9]{9}/',
            'password' => 'string|min:6|confirmed|required',
            'msgId' => 'required|numeric|exists:sms_messages,id',
        ];
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
}
