<?php

namespace App\Http\Requests;

use App\Rules\DoctorSpecRule;
use App\Rules\ExistRule;
use App\Rules\UserRateRule;

class NurseForm extends UserForm
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() { // authorization is happening by policies
        return parent::authorize();
    }

    public function messages()
    {
        return [
            "short_description.max" => "يحب أن يكون الطول الأقصى للوصف 500 حرف",
            "assigned_at.before_or_equal" => "تاريخ التعين لم يحدث بعد",
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
            return array_merge(parent::rules() , [
                'departement_id' => ["integer" , new ExistRule()],
                'short_description' => "required|string|max:500",

                'rate' => [
                    "required",
                    "integer",
                    new UserRateRule(),
                ],

                'specialization' => [
                    "required",
                    "integer",
                    new DoctorSpecRule(),
                ],
                "assigned_at" => "required|date|before_or_equal:today"
            ]);
            
        } else if ( $this->isMethod("put") ) {
            return array_merge(parent::rules() , [
                'departement_id' => ["integer" , new ExistRule()],
                'doctor_id' => ["integer" , new ExistRule()],
                'short_description' => "string|max:500",

                'rate' => [
                    "integer",
                    new UserRateRule(),
                ],  
                "assigned_at" => "date|before_or_equal:today",

                'specialization' => [
                    "integer",
                    new DoctorSpecRule(),
                ],
            ]);
        }
    }
}
