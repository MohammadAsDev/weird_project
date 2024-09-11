<?php

namespace App\Rules;

use App\Models\Appointement;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class AppointementDateRule implements Rule
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

    
    private function checkDateValidation($appointements_list , $date) {
        $date = Carbon::createFromFormat("Y-m-d H:i:s" , $date);
        $min_diff_in_minutes = 60;
        foreach ($appointements_list as $appointement) {
            $appointement_date = Carbon::createFromFormat("Y-m-d H:i:s" , $appointement["date"]);
            $diff = $date->diffInMinutes($appointement_date);
            if ( $diff <= $min_diff_in_minutes ) {
                return false;
            }
        }
        return true;
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
        $date = Carbon::createFromDate($value);
        $appointements = Appointement::where(
            "date",
            ">",
            Carbon::now()->today()->toDateTimeString()
        )->get()->toArray();  
        
        if ( ! $this->checkDateValidation($appointements , $value) ) {
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
        return 'selected appointement date is not empty';
    }
}
