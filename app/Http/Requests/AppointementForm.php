<?php

namespace App\Http\Requests;

use App\Rules\AppointementStatusRule;
use App\Rules\ExistRule;
use App\Rules\PeriodRule;
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
                "doctor_id" => [
                    "required",
                    "integer",
                    new ExistRule()
                ],
                "date" => [
                    "required",
                    "date",
                    "after:today" , 
                ],
                "period" => [
                    "required",
                    "string",
                    new PeriodRule()
                ]
            ];
        } else if ( $this->isMethod('PUT') ) {
            return [
                "status" => [
                    "required",
                    "integer",
                    new AppointementStatusRule()
                ]
            ];
        }
        
    }
}
