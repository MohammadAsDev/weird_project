<?php

namespace App\Models;

use App\Enums\BloodType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        "blood_type",
        "aspirin_allergy"
    ];

    protected $primaryKey = "user_id";
    public $incrementing = False;

    protected $casts = [
        "blood_type" => BloodType::class
    ];

    public function user() {
        return $this->belongsTo(User::class , "user_id" , "id");
    }

    public function appointements() {
        return $this->hasMany(Appointement::class , 'patient_id' , 'user_id');
    }


    public function tests() {
        return $this->hasMany(RoutineTest::class , "patient_id" , "user_id");
    }
}
