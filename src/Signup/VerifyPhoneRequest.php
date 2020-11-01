<?php

namespace Larapress\Auth\Signup;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPhoneRequest  extends FormRequest {
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
            'captcha' => 'required|captcha_api:'.$this->request->get('key'),
            'accept_terms' => 'required|boolean|in:1,true',
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
}
