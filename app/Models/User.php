<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'gender',
        'birth_date',
        'address',
        'profile_picture_path',
        'ssn',
        "role_id"
    ];

    protected $attributes = [
        'profile_picture_path' => 'profiles/pictures/user_template.svg',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role_id' => Role::class,
    ];

    /**
     * Get user's role, from the database
     * 
     * @var int
     */
     public function getRoleID() {
        return $this->role_id;
     }

     public function verifyToken() {
        return $this->belongsTo(VerifyToken::class , "id" , "user_id");
     }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            "role" => $this->getRoleID(),
            "email" => $this->email,
        ];
    }

    /**
     * Always encrypt password when it is updated.
     *
     * @param $value
     * @return string
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }


    /**
     * always keep emails as lowercase.
     *
     * @param $value
     * @return string
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }


    /**
     * Determine if the user has verified their email address
     * 
     * @return bool
    */
    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);     
    }

    /**
     * Mark the given user's email as verified
     * 
     * @return bool
    */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            "email_verified_at" => $this->freshTimestamp()
        ])->save();
    }


    public function appointements() {
        return $this->hasMany(Appointement::class , 'patient_id' , 'id');
    }


    public function tests() {
        return $this->hasMany(RoutineTest::class , "patient_id" , "id");
    }
}
