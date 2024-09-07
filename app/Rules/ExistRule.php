<?php

namespace App\Rules;

use App\Models\Clinic;
use App\Models\Departement;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;

class ExistRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //
        $model = null;
        switch(strtolower($attribute)) {
            case "departement_id":
                $model = Departement::where("id" , $value)->first();
                break;
            case "doctor_id":
                $model = Doctor::where("user_id" , $value)->first();
                break;
            case "clinic_id":
                $model = Clinic::where("id" , $value)->first();
                break;
            case "ssn":
                $model = User::where("ssn" , $value);
                break;
        }
        return $model != null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'selected object does not exist';
    }
}
