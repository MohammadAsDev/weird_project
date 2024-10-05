<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchTokenForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
            "token.min" => "يجب أنْ يكون الكود من 10 محارف",
            "token.max" => "يجب أنْ يكون الكود من 10 محارف"
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
                "ssn" => "required|string|min:11|max:11"
            ];
        }
    }
}
