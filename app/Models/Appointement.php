<?php

namespace App\Models;

use App\Enums\AppointementStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointement extends Model
{
    use HasFactory;
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'date',
        'period',
        'status'  
    ];

    protected $attributes = [
        "status" => AppointementStatus::NEED_ACK,
    ];

    protected $casts = [
        'status' => AppointementStatus::class,
        'date' => 'date',
    ];

    public function patient() {
        return $this->belongsTo(User::class , "patient_id" , "id");
    }

    public function doctor() {
        return $this->belongsTo(Doctor::class  , "doctor_id" , "user_id");
    }
}
