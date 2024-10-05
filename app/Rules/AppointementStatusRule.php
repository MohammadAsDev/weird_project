<?php

namespace App\Rules;

use App\Enums\AppointementStatus;
use App\Models\Appointement;
use Illuminate\Contracts\Validation\Rule;

class AppointementStatusRule implements Rule
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
        return 
            $value == AppointementStatus::ACCEPTED->value 
            || 
            $value == AppointementStatus::REJECTED->value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'حالة الموعد يجب أنْ تكون قبول الموعد أو رفضه';
    }
}
