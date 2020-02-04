<?php

namespace Larapress\Auth\Signin;

use Illuminate\Foundation\Http\FormRequest;

class SigninRequest extends FormRequest
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
            'username' => 'required',
            'password' => 'required'
        ];
    }

    public function getCredentials() {
        return [
            'username' => $this->request->get('username'),
            'password' => $this->request->get('password')
        ];
    }
}