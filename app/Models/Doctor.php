<?php

namespace App\Models;

use App\Enums\MedicalSpecialization;
use App\Enums\Rate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id',
        'departement_id',
        'specialization',
        'rate',
        'short_description',
        'assigned_at',
    ];
    protected $primaryKey = "user_id";
    public $incrementing = False;

    protected $casts = [
        "rate" => Rate::class,
        "specialization" => MedicalSpecialization::class,
    ];

    public function user() {
        return $this->belongsTo(User::class ,"user_id" , "id");
    }

    public function departement() {
        return $this->belongsTo(Departement::class , "departement_id" , "id");
    }

    public function nurses() {
        return $this->hasMany(Nurse::class , "doctor_id" , "user_id");
    }

    public function clinics() {
        return $this->belongsTo(Clinic::class , "user_id" , "doctor_id");
    }

    public function appointements() {
        return $this->hasMany(Appointement::class , "doctor_id" , "user_id");
    }

    public function tests() {
        return $this->hasMany(RoutineTest::class , "doctor_id" , "user_id");
    }
}
