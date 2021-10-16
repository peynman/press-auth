<?php

namespace Larapress\Auth\Services\Signup\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate Phone number with verification code sent
 *
 * @queryParam email required_without:phone the email
 * @queryParam phone required_without:email the phone number with/without country code
 * @queryParam code required the code recieved by sms
 */
class VerifyCheckRequest extends FormRequest
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
            'code' => 'required',
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
        return $this->Get('email');
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getCode()
    {
        return $this->get('code');
    }
}
