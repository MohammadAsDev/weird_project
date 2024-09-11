<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifyToken extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "token",
        "is_verified"
    ];

    protected $attributes = [
        "is_verified" => false ,
    ];

    public function user() {
        return $this->belongsTo(User::class ,"user_id" , "id");
    }
}
