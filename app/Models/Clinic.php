<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'departement_id',
        'clinic_code',
        'clinic_latitude',
        'clinic_longitude'
    ];

    public function departement() {
        return $this->belongsTo(Departement::class, "departement_id" , "id");
    }

}
