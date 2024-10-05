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

    public function messages()
    {
        return [
            "breathing_rate.min" => "يجب أنْ يكون معدل التنفس بين 0 و 100",
            "breathing_rate.max" => "يجب أنْ يكون معدل التنفس بين 0 و 100",
            "breathing_rate.numeric" => "يجب أنْ يكون معدل التنفس قيمة رقميّة",
            "breathing_rate.required" => "يجب أنْ يكون معدل التنفس غير فارغ",

            "pulse_rate.min" => "يجب أنْ يكون معدل ضربات القلب بين 0 و 100",
            "pulse_rate.max" => "يجب أنْ يكون معدل ضربات القلب بين 0 و 100",
            "pulse_rate.numeric" => "يجب أنْ يكون معدل ضربات القلب قيمة رقميّة",
            "pulse_rate.required" => "يجب أنْ يكون معدل ضربات القلب غير فارغ",


            "body_temperature.min" => "يجب أنْ تكون درجة الحرارة بين 0 و 100",
            "body_temperature.max" => "يجب أنْ تكون درجة الحرارة بين 0 و 100",
            "body_temperature.numeric" => "يجب أنْ تكون درجة حرارة الجسم قيمة رقميّة",
            "body_temperature.required" => "يجب أنْ تكون درجة حرارة الجسم غير فارغة",

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
