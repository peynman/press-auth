<?php

namespace Larapress\Auth\Signup;

use Illuminate\Foundation\Http\FormRequest;

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
            'phone' => 'required|numeric|',
            'password' => 'string|min:6|confirmed|required',
            'msg_id' => 'required|numeric|exists:sms_messages,id',
        ];
    }


    /**
     * Undocumented function
     *
     * @return String
     */
    public function getPhone() {
        return $this->get('phone');
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getPassword() {
        return $this->get('password');
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getMessageID() {
        return $this->get('msg_id');
    }
}
