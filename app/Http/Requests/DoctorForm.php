<?php

namespace App\Http\Requests;

use App\Rules\DoctorSpecRule;
use App\Rules\ExistRule;
use App\Rules\UserRateRule;
use Illuminate\Validation\Rules\Exists;

class DoctorForm extends UserForm
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {   // authorization is happening by policies
        return parent::authorize();   
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() { 
        if ( $this->isMethod("POST") ) {
            return array_merge(parent::rules(), [
                'short_description' => "required|string|max:500",
                'rate' => [
                    "required",
                    "integer",
                    new UserRateRule(),
                ],
                'specialization' => [
                    "required",
                    "integer",
                    new DoctorSpecRule()
                ],
            ]);
            
        } else if ( $this->isMethod("PUT") ) {
            return array_merge(parent::rules() , [
                'short_description' => "string|max:500",
                'departement_id' => ['integer', new ExistRule()],
                'rate' => [
                    "integer",
                    new UserRateRule(),
                ],

                'specialization' => [
                    "integer",
                    new DoctorSpecRule(),
                ],
            ]);
        }
        
    }
}
