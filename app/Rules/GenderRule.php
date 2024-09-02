<?php

namespace App\Rules;

use App\Enums\Gender;
use Illuminate\Contracts\Validation\Rule;

class GenderRule implements Rule
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
        return in_array($value, array_column(Gender::cases() , "value"));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'gender could be male(0) or female(1)';
    }
}
