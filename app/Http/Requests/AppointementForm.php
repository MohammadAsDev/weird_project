<?php

namespace App\Http\Requests;

use App\Rules\AppointementDateRule;
use App\Rules\ExistRule;
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
                "clinic_id" => [
                    "required",
                    "integer",
                    new ExistRule()
                ],
                "date" => [
                    "required",
                    "date",
                    "after:today" , 
                    new AppointementDateRule()
                ]
            ];
        } else if ( $this->isMethod('PUT') ) {
            return [
                'next_date' => [
                    "required","date","after:tody",
                    new AppointementDateRule()
                ],
                'status'  => "required|integer",
                "attachement.breathing_rate" => "required|numeric|min:0|max:100",
                // 'attachement.blood_pressure' => "required|numeric|",
                'attachement.body_temperature' => "required|numeric|min:0|max:100",
                'attachement.pulse_rate' => "required|numeric|max:100|min:0",
                'attachement.medical_notes' => "string",
                "attachement.prescription" => "string"

            ];
        }
        
    }
}
