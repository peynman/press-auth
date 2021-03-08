<?php

namespace Larapress\Auth\Signup;

use Illuminate\Foundation\Http\FormRequest;
use Larapress\CRUD\Exceptions\ValidationException;
use Mews\Captcha\Facades\Captcha;

class VerifyPhoneRequest extends FormRequest
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
            'phone' => 'required|numeric|regex:/(09)[0-9]{9}/|digits:11',
            'key' => 'required|string',
            'captcha' => 'required|captcha_api:'.$this->request->get('key').',default',
            'accept_terms' => 'required|boolean|in:1,true',
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

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getPhone()
    {
        return $this->get('phone');
    }
}
