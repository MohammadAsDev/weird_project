<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RoutineTestForm extends FormRequest
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
        if ( $this->isMethod("put") ) {
            return [
                'breathing_rate' => "numeric",
                'blood_pressure' => "numeric",
                'body_temperature' => "numeric",
                'pulse_rate' => "numeric",
                'medical_notes' => "string",
                "prescription" => "string"
            ];
        }
    }
}
