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
        if ( $this->isMethod("post") ) {
            return [
                "breathing_rate" => "required|numeric|min:0|max:100",
                'body_temperature' => "required|numeric|min:0|max:100",
                'pulse_rate' => "required|numeric|max:100|min:0",
                'medical_notes' => "string",
                "prescription" => "string"
            ];
        }
        else if ( $this->isMethod("put") ) {
            return [
                'breathing_rate' => "numeric|min:0|max:100",
                'body_temperature' => "numeric|min:0|max:100",
                'pulse_rate' => "numeric|min:0|max:100",
                'medical_notes' => "string",
                "prescription" => "string"
            ];
        }
    }
}
