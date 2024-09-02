<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AppointementForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {   // we are using policies
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
        if ( $this->isMethod('POST') ){
            return [
                "clinic_id" => "required|integer",
                "doctor_id" => "required|integer",
                "date" => "required|date|after:today"
            ];
        } else if ( $this->isMethod('PUT') ) {
            return [
                'next_date' => "required|date|after:tody",
                'status'  => "required|integer"
            ];
        }
        
    }
}
