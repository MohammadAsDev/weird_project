<?php

namespace App\Rules;

use App\Enums\DoctorSpecialization;
use Illuminate\Contracts\Validation\Rule;

class DoctorSpecRule implements Rule
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
        return in_array($value , array_column(DoctorSpecialization::cases() , "value"));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Doctor specialization is not specified';
    }
}
