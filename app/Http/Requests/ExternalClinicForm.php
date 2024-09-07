<?php

namespace App\Http\Requests;

use App\Rules\ExistRule;

class ExternalClinicForm extends ClinicForm
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {       // we are using policies
        return parent::authorize();
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ( $this->isMethod('POST') ) {
            return array_merge(parent::rules() , [
                'clinic_longitude' => 'required|between:-180,180',
                'clinic_latitude' => 'required|between:-90,90',
                'doctor_id' => ['required','integer' , new ExistRule()],
            ]);
        } else if ( $this->isMethod('PUT') ) {
            return parent::rules();
        }        
    }
}
