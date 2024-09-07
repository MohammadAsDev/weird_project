<?php

namespace App\Http\Requests;

use App\Rules\BloodTypeRule;
use App\Rules\ExistRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PreCheckForm extends FormRequest
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


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ssn'  => ["required","string","regex:/[0-9]{11}/" , new ExistRule()],
            'blood_type' => [
                'required',
                'integer',
                new BloodTypeRule(),
            ] , 
            'aspirin_allergy' => "required|boolean",
        ];
    }
}
