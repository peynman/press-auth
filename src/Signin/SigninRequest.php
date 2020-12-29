<?php

namespace Larapress\Auth\Signin;

use Illuminate\Foundation\Http\FormRequest;
use Larapress\CRUD\Exceptions\ValidationException;
use Mews\Captcha\Facades\Captcha;

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
            'password' => 'required',
            'key' => 'required|string',
            'captcha' => 'required|captcha_api:'.$this->request->get('key').',default',
        ];
    }

    /**
     * Undocumented function
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, [
            'captcha' => Captcha::create('default', true)
        ]);
    }

    public function getCredentials() {
        return [
            'username' => $this->request->get('username'),
            'password' => $this->request->get('password')
        ];
    }

    public function getUsername() {
        return $this->request->get('username');
    }

    public function getPassword() {
        return $this->request->get('password');
    }
}
