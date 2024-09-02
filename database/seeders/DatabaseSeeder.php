<?php

namespace Database\Seeders;

use App\Enums\ClinicType;
use App\Enums\DoctorSpecialization;
use App\Enums\Gender;
use App\Enums\MedicalSpecialization;
use App\Enums\Rate;
use App\Enums\Role;
use App\Rules\DoctorSpecRule;
use Error;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        //Create Pre-defined Roles
        \App\Models\Role::create([
            "role_id" => Role::ADMIN,
            "role_description" => "Amdinistration"
        ]);
        \App\Models\Role::create([
            "role_id" => Role::STAFF,
            "role_description" => "System and Users Management"
        ]);
        \App\Models\Role::create([
            "role_id" => Role::DOCTOR,
            "role_description" => "Doctors"
        ]);
        \App\Models\Role::create([
            "role_id" => Role::NURSE,
            "role_description" => "Nurses"
        ]);
        \App\Models\Role::create([
            "role_id"  => Role::PATIENT,
            "role_description" => "Patients"
        ]);


        $departement = \App\Models\Departement::create([
            "name" => "seed departement",
            "description" => "seed-created departement",
            "specialization" => MedicalSpecialization::SURGERY
        ]);

        // Create Admin User
        \App\Models\User::create([
            'first_name' => 'Mohammad',
            'last_name' => 'Hamdan',
            'phone_number' => '0997194988',
            'email' => 'admin@gmail.com',
            'password' => "admin",
            'birth_date' => "2001-12-04",
            'address' => "Homs",
            "role_id" => Role::ADMIN,
            "gender" => Gender::MALE,
        ]);

        // Create a new Doctor
        $doctor_user = \App\Models\User::create([
            'first_name' => 'SeedDoctor',
            'last_name' => 'SeedDoctor',
            'phone_number' => '0912312312',
            'email' => 'seed.Doctor@gmail.com',
            'password' => "123456789",
            'birth_date' => "2001-12-04",
            'address' => "Homs",
            "role_id" => Role::DOCTOR,
            "gender" => Gender::MALE,
        ]);


        $doctor = \App\Models\Doctor::create([
            'user_id' => $doctor_user->id,
            'departement_id' => $departement->id,
            'specialization' => MedicalSpecialization::ANESTHESIOLOGY,
            'rate' => Rate::GOOD,
            'short_description' => "testing doctor",
        ]);


        // Create a new Clinic
        \App\Models\Clinic::create([
            'doctor_id' => $doctor->user_id,
            'departement_id' => $departement->id,
            'clinic_code' => "TEST",
            "clinic_type" => ClinicType::INTERNAL
        ]);

        // Create a new Nures
        $nurse_user = \App\Models\User::create([
            'first_name' => 'SeedNurse',
            'last_name' => 'SeedNurse',
            'phone_number' => '0912345612',
            'email' => 'seed.Nurse@gmail.com',
            'password' => "123456789",
            'birth_date' => "2001-12-04",
            'address' => "Homs",
            "role_id" => Role::NURSE,
            "gender" => Gender::MALE,
        ]);

        \App\Models\Nurse::create([
            'rate' => Rate::GOOD,
            'departement_id' => $departement->id,
            'doctor_id' => $doctor->user_id,
            'specialization' => MedicalSpecialization::INTERNAL,
            'short_description' => "testing nurse",
            'user_id' => $nurse_user->id,
        ]);


        // Create a new Patient
        $patient_user = \App\Models\User::create([
            'first_name' => 'SeedPatient',
            'last_name' => 'SeedPatient',
            'phone_number' => '0912345678',
            'email' => 'seed.Patient@gmail.com',
            'password' => "123456789",
            'birth_date' => "2001-12-04",
            'address' => "Homs",
            "role_id" => Role::PATIENT,
            "gender" => Gender::MALE,
            "ssn" => "00000000000"
        ]);

        \App\Models\Patient::create([
            'blood_type' => 2,
            'aspirin_allergy' => true,
            'user_id' => $patient_user->id,
        ]);

    }
}
