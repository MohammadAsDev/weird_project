<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PatientForm extends UserForm
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


    public function messages()
    {
        return [
            "ssn.regex" => "يجب أنْ يكون الرقم الوطني مؤلف من 11 خانة",
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ( $this->isMethod("post") ) {
            return array_merge(parent::rules() , parent::rules() , [
                'ssn' => "required|string|unique:users|regex:/[0-9]{11}/",
            ]);
            
        } else if ( $this->isMethod("put") ) {
            return array_merge(parent::rules() , [
                'ssn' => "string|unique:users|regex:/[0-9]{11}/",
            ]);
        }
    }
}
