<?php

namespace Larapress\Auth\Password;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyProp old string required Old password.
 * @bodyProp password string required New password.
 * @bodyProp password_confirmation string required New password confirmed.
 */
class UpdatePasswordRequest extends FormRequest
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
            'old' => 'required|string',
            'password' => 'string|min:6|confirmed|required',
        ];
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getOldPassword()
    {
        return $this->request->get('old');
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getNewPassword()
    {
        return $this->request->get('password');
    }
}
