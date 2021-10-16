<?php

namespace Larapress\Auth\Services\Signup\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Larapress\CRUD\Exceptions\ValidationException;
use Mews\Captcha\Facades\Captcha;

/**
 * Request to verify a phone number with default SMS Gateway
 *
 * @bodyParam email string required_without:phone the email
 * @bodyParam phone string required_without:email the phone number with/without country code
 * @bodyParam key string required the captcha key received by visiting /siginin page (Captcha safe source)
 * @bodyParam captcha string required the captcha answer
 * @bodyParam accept_terms boolean required is term of service accepted
 */
class VerifyRequest extends FormRequest
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
            'phone' => 'required_without:email|numeric|regex:/(09)[0-9]{9}/|digits:11',
            'key' => 'required|string',
            'captcha' => 'required|captcha_api:'.$this->request->get('key').',default',
            'acceptTerms' => (config('larapress.auth.signup.should_accept_terms') ? 'required' : 'nullable').'|in:1,true',
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
