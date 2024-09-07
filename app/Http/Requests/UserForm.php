<?php

namespace App\Http\Requests;

use App\Rules\BirthYearRule;
use App\Rules\GenderRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()     // we are using policies
    {
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
        if ( $this->isMethod('POST') ) {
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
                    'required',
                    'string',
                    new BirthYearRule(),
                ],        
            ];
        } else if ( $this->isMethod('PUT') ) {
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
                    'date',
                    new BirthYearRule(),
                ],

            ];
        }
    }
}
