<?php

namespace App\Http\Requests;

use App\Rules\ExistRule;

class InternalClinicForm extends ClinicForm
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    
    public function authorize() {       // we are using policies
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function rules()
    {
        if ( $this->isMethod('POST') ) {
            return array_merge(parent::rules(), [
                'doctor_id' => ['required','integer' , new ExistRule()],
                'clinic_code' => 'required|string|unique:clinics',
            ]);
        } else if ( $this->isMethod('PUT') ) {
            return parent::rules();
        }
    }
}
