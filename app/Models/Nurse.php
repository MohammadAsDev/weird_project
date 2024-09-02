<?php

namespace App\Models;

use App\Enums\MedicalSpecialization;
use App\Enums\Rate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nurse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'departement_id',
        'specialization',
        'rate',
        'short_description'
    ];

    protected $primaryKey = "user_id";
    public $incrementing = False;

    protected $casts = [
        'rate' => Rate::class,
        'specialization' => MedicalSpecialization::class,
    ];

    public function user() {
        return $this->belongsTo(User::class , "user_id" , "id");
    }

    public function doctor() {
        return $this->belongsTo(Doctor::class , "doctor_id" , "user_id");
    }

    public function departement() {
        return $this->belongsTo(Departement::class);
    }
}
