<?php

namespace App\Models;

use App\Enums\MedicalSpecialization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "specialization",
        "description",
    ];

    protected $casts = [
        "specialization" => MedicalSpecialization::class
    ];

    public function clinics() {
        return $this->hasMany(Clinic::class , "departement_id" , "id");
    }
}
