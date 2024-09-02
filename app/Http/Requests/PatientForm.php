<?php

namespace App\Http\Requests;

use App\Rules\BirthYearRule;
use App\Rules\GenderRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PatientForm extends FormRequest
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
                'address' => "required|string|max:100|min:2",
                
                'gender' => [
                    "required",
                    "integer",
                    new GenderRule()
                ],

                'birth_date' => [
                    'date',
                    'string',
                    new BirthYearRule(),
                ],

                'ssn' => "required|string|unique:users|regex:/[0-9]{11}/",
            ];
            
        } else if ( $this->isMethod("put") ) {
            return [
                'first_name' => 'string|max:255|min:2|regex:/^[a-zA-Z\s]+$/',
                'last_name'  => 'string|max:255|min:2|regex:/^[a-zA-Z\s]+$/',
                'email' => 'email|unique:users',
                'password' => "string|max:500|min:9",
                'phone_number' => 'unique:users|regex:/^0[0-9]{9}/', 
                'address' => "string|max:100|min:2",
                
                'gender' => [
                    "integer",
                    new GenderRule()
                ],

                'birth_date' => [
                    'string',
                    new BirthYearRule(),
                ],

                'ssn' => "required|string|unique:users|regex:/[0-9]{11}/",

            ];
        }
    }
}
