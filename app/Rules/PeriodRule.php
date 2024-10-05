<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PeriodRule implements Rule
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
        $delimiter = "-";
        $period_arr = [(int)strtok($value , $delimiter)];
        for($tok = strtok($delimiter) ; $tok ; $tok = strtok($delimiter)){
            array_push($period_arr , (int)$tok);
        }
        if (count($period_arr) != 2) {
            return false;
        }
        
        if ( 
                $period_arr[0] < 9
                || 
                $period_arr[1] > 16 
                || 
                $period_arr[0] > $period_arr[1] 
                ||
                abs($period_arr[0] - $period_arr[1]) != 1 
        )  {
            return false;
        }   

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'صياغة الفترة غير صحيحة ("بدء-إنتهاء" خلال وقت العمل)';
    }
}
