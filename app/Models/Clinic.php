<?php

namespace App\Models;

use App\Enums\ClinicType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_type',
        'doctor_id',
        'departement_id',
        'clinic_code',
        'clinic_latitude',
        'clinic_longitude'
    ];

    protected $casts = [
        'clinic_type' => ClinicType::class,
    ];

    public function doctor() {
        return $this->belongsTo(Doctor::class , "doctor_id" , "user_id");
    }

    public function departement() {
        return $this->belongsTo(Departement::class, "departement_id" , "id");
    }

    public function appointements() {
        return $this->hasMany(Appointement::class , "clinic_id", "id");
    }
}
