<?php

namespace App\Models;

use App\Enums\AppointementStatus;
use App\Enums\ClinicType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointement extends Model
{
    use HasFactory;
    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'date',
        'next_date',
        'status'  
    ];

    protected $attributes = [
        "status" => AppointementStatus::WAITED,
    ];

    protected $casts = [
        'status' => AppointementStatus::class,
        'date' => 'datetime:Y-m-d H:i:s',
        'next_date' => 'datetime:datetime:Y-m-d H:i:s',
    ];

    public function patient() {
        return $this->belongsTo(Patient::class , "patient_id" , "user_id");
    }

    public function doctor() {
        return $this->belongsTo(Doctor::class  , "doctor_id" , "user_id");
    }

    public function clinic() {
        return $this->belongsTo(Clinic::class , "clinic_id" , "id");
    }
}
