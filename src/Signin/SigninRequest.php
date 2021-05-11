<?php

namespace Larapress\Auth\Signin;

use Illuminate\Foundation\Http\FormRequest;
use Larapress\CRUD\Exceptions\ValidationException;
use Mews\Captcha\Facades\Captcha;

/**
 * Request params to sign in and receive an API Token
 *
 * @bodyParam username required the username/email/phone-number to use
 * @bodyParam password required the password
 * @bodyParam key required the captcha key received by visiting /siginin page (Captcha safe source)
 * @bodyParam captcha required the captcha answer
 */
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
     * Override default validation exception and include a new captcha
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @throws ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, [
            'captcha' => Captcha::create('default', true)
        ]);
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getCredentials()
    {
        return [
            'username' => $this->request->get('username'),
            'password' => $this->request->get('password')
        ];
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->request->get('username');
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->request->get('password');
    }
}
