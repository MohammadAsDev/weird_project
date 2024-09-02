<?php

namespace App\Http\Requests;

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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ( $this->isMethod('PUT') ) {
            return [
                'clinic_type' => 'integer',
                'clinic_longitude' => 'between:-180,180',
                'clinic_latitude' => 'between:-90,90',
                'doctor_id' => 'integer',
                'departement_id' => 'integer',
                'clinic_code' => 'string|unique:clinics',
            ];
        }
        
    }
}
