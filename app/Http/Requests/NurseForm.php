<?php

namespace App\Http\Requests;

use App\Rules\BirthYearRule;
use App\Rules\DoctorSpecRule;
use App\Rules\GenderRule;
use App\Rules\UserRateRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class NurseForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() { // authorization is happening by policies
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ( $this->isMethod("post") ) {
            return [
                'first_name' => 'required|string|max:255|min:2|regex:/^[a-zA-Z\s]+$/',
                'last_name'  => 'required|string|max:255|min:2|regex:/^[a-zA-Z\s]+$/',
                'email' => 'required|email|unique:users',
                'password' => "required|string|max:500|min:9",
                'phone_number' => 'required|unique:users|regex:/^0[0-9]{9}/', 
                'short_description' => "required|string|max:500",
                'address' => "required|string|max:100|min:2",
                // 'departement_id' => "required|integer",
                'doctor_id' => "required|integer",

                'gender' => [
                    "required",
                    "integer",
                    new GenderRule()
                ],

                'birth_date' => [
                    'required',
                    'date',
                    'string',
                    new BirthYearRule(),
                ],

                'rate' => [
                    "required",
                    "integer",
                    new UserRateRule(),
                ],

                'specialization' => [
                    "required",
                    "integer",
                    new DoctorSpecRule(),
                ],

            ];
            
        } else if ( $this->isMethod("put") ) {
            return [
                'first_name' => 'string|max:255|min:2|regex:/^[a-zA-Z\s]+$/',
                'last_name'  => 'string|max:255|min:2|regex:/^[a-zA-Z\s]+$/',
                'email' => 'email|unique:users',
                'password' => "string|max:500|min:9",
                'phone_number' => 'unique:users|regex:/^0[0-9]{9}/', 
                'short_description' => "string|max:500",
                'address' => "string|max:100|min:2",
                'departement_id' => "integer",
                'doctor_id' => "integer",

                'gender' => [
                    "integer",
                    new GenderRule()
                ],

                'birth_date' => [
                    'string',
                    new BirthYearRule(),
                ],

                'rate' => [
                    "integer",
                    new UserRateRule(),
                ],  


                'specialization' => [
                    "integer",
                    new DoctorSpecRule(),
                ],
            ];
        }
    }
}
