<?php

namespace Larapress\Auth\Password;

use Illuminate\Foundation\Http\FormRequest;

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

    public function getOldPassword()
    {
        return $this->request->get('old');
    }

    public function getNewPassword()
    {
        return $this->request->get('password');
    }
}
