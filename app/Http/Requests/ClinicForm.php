<?php

namespace App\Http\Requests;

use App\Rules\ExistRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClinicForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {       // we are using policies
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
    }

    public function messages()
    {
        return [
            "clinic_code.unique" => "رمز الغرفة هو رمز فريد في المشفى",
            
        ];
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
                'clinic_code' => 'required|string|unique:clinics',
                'departement_id' => ['integer' , new ExistRule()],
            ];
        } else if ( $this->isMethod('PUT') ) {
            return [
                'departement_id' => ['integer' , new ExistRule()],
                'clinic_code' => 'string|unique:clinics',
            ];
        }
        
    }
}
