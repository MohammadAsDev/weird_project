<?php

use App\Http\Controllers\AppointementController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Controllers\Nurses\NurseController;
use App\Http\Controllers\Patients\PatientController;
use App\Models\Appointement;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

const PARAM_EXPRESSIONS = [
    "id" => "[0-9]+",
];

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('confirm', [AuthController::class, 'confirm']);
});

Route::group([  // doctors end-points
    'middleware' => 'api',
    'prefix' => 'doctors'
] , function ($router) {

    // Doctors CRUD functionality
    Route::get('/', [DoctorController::class , 'index']);
    Route::post('/', [DoctorController::class , 'create']);
    Route::get('/{id}', [DoctorController::class, 'read'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::put('/{id}', [DoctorController::class, 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::delete('/{id}', [DoctorController::class, 'delete'])->where("id" , PARAM_EXPRESSIONS["id"]);

    // Nurses working with a specific doctor
    Route::get('/{id}/nurses', [DoctorController::class, 'listNurses'])->where("id" , PARAM_EXPRESSIONS["id"]);

    // Appointements for a specific doctor
    Route::get('/{id}/appointements' , [PatientController::class , 'appointements'])->where("id" , PARAM_EXPRESSIONS["id"]);

});


Route::group([      // nurses end-points
    'prefix' => 'nurses',
    'middleware' => 'api',
] , function($router) {

    // Nurses CRUD functionality
    Route::get('/', [NurseController::class , 'index']);
    Route::get('/{id}', [NurseController::class, 'read'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::get('/{id}/doctor', [NurseController::class, 'doctor'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::put('/{id}', [NurseController::class, 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::delete('/{id}', [NurseController::class, 'delete'])->where("id" , PARAM_EXPRESSIONS["id"]);
});


Route::group([      // patients end-points
    'prefix' => 'patients',
    'middleware' => 'api',
] , function($router) {

    // Patients CRUD functionality
    Route::get('/', [PatientController::class , 'index']);
    Route::post('/', [PatientController::class , 'create']);
    Route::get('/{id}', [PatientController::class, 'read'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::put('/{id}', [PatientController::class, 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::delete('/{id}', [PatientController::class, 'delete'])->where("id" , PARAM_EXPRESSIONS["id"]);
    
    // Prechecks
    Route::get('/prechecks' , [PatientController::class , 'withNoPrecheck']);
    Route::post('/prechecks' , [PatientController::class, 'set_precheck']);

    // Appointements for a specific patient
    Route::get('/{id}/appointements' , [PatientController::class , 'appointements'])->where("id" , PARAM_EXPRESSIONS["id"]);
});


Route::group([      // departements end-points
    'prefix' => 'departements',
    'middleware' => 'api',
] , function($router) {

    // Departements CRUD functionality
    Route::get('/', [DepartementController::class , 'index']);
    Route::post('/', [DepartementController::class , 'create']);
    Route::get('/{id}', [DepartementController::class, 'read'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::put('/{id}', [DepartementController::class, 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::delete('/{id}', [DepartementController::class, 'delete'])->where("id" , PARAM_EXPRESSIONS["id"]);
    
    // Doctors in a specific departements
    Route::get('/{id}/doctors' , [DepartementController::class , "listDoctors"])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::post('/{id}/doctors' , [DepartementController::class , "createDoctor"])->where("id" , PARAM_EXPRESSIONS["id"]);
    
    // Nurses in a specific departements
    Route::get('/{id}/nurses' , [DepartementController::class , "listNurses"])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::post('/{id}/nurses' , [DepartementController::class , "createNurse"])->where("id" , PARAM_EXPRESSIONS["id"]);
});


Route::group([          // clinics end-points
    'prefix' => 'clinics',
    'middleware' => 'api',
], function($router) {

    // Clinics CRUD functionality
    Route::get('/', [ClinicController::class , 'index']);
    Route::put('/{id}', [ClinicController::class , 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::delete('/{id}', [ClinicController::class , 'delete'])->where("id" , PARAM_EXPRESSIONS["id"]);

    // Appointements for a specific clinic 
    Route::get('/{id}/appointements' , [ClinicController::class , 'appointements'])->where("id" , PARAM_EXPRESSIONS["id"]);

    // Interal clinics
    Route::get('/internal' , [ClinicController::class , 'listInternalClinics']);        // patients should see that
    Route::get('/internal/{id}', [ClinicController::class , 'readInternalClinic'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::post('/internal' , [ClinicController::class , 'createInternalClinic']);

    // External clinics
    Route::get('/external' , [ClinicController::class , 'listExternalClinics']);        // patients should see that
    Route::get('/external/{id}', [ClinicController::class , 'readExternalClinic'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::post('/external' , [ClinicController::class , 'createExternalClinic']);
    
});


Route::group([
    "prefix" => 'appointements',
    'middleware' => 'api',
], function ($router) {
    Route::get("/" , [AppointementController::class , 'index']);
    Route::post("/" , [AppointementController::class , 'create']);

    Route::get("/me" , [AppointementController::class , "me"]);
    Route::get("/me/{id}" , [AppointementController::class , "readAppointement"]);
    Route::get("/patients" , [AppointementController::class , "patients"]);
    Route::get("/patients/{id}" , [AppointementController::class , "readPatient"]);
    Route::put("/patients/{id}"  , [AppointementController::class , 'submit']);      // only doctors can do that


    Route::get("/clinics/{id}" , [AppointementController::class , 'listClinicAppointements']);     //patients should see that
    Route::get("/doctors/{id}" , [AppointementController::class , 'listDoctorAppointements']);     //patients should see that  
});