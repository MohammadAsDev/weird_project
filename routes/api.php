<?php

use App\Http\Controllers\AppointementController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Controllers\Nurses\NurseController;
use App\Http\Controllers\Patients\PatientController;
use App\Http\Controllers\RoutineTestController;
use App\Http\Controllers\VerifyTokenController;

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
});

Route::group([
    'middleware' => ['api'],
    'prefix' => 'statistics'
], function ($router) {
    // see important statistics about the hospital (PATIENT)
    Route::get('/best_doctors' , [DoctorController::class, 'bestDoctors']);
});

Route::group([  // doctors end-points
    'middleware' => ['api'],
    'prefix' => 'doctors'
] , function ($router) {

    // Doctors CRUD functionality   (ADMIN)
    Route::get('/', [DoctorController::class , 'index']);
    Route::post('/', [DoctorController::class , 'create']);
    Route::get('/{id}', [DoctorController::class, 'read'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::put('/{id}', [DoctorController::class, 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::delete('/{id}', [DoctorController::class, 'delete'])->where("id" , PARAM_EXPRESSIONS["id"]);

    // Search on doctors (By their names)  (PATIENT)
    Route::get("/search" , [DoctorController::class , "search"]);

    // Current Doctor end-points    (DOCTOR)
    Route::get("/me" , [DoctorController::class , "me"]);
    Route::put("/me" , [DoctorController::class , "updateMe"]);

});


Route::group([      // nurses end-points
    'prefix' => 'nurses',
    'middleware' => ['api'],
] , function($router) {

    // Nurses CRUD functionality    (ADMIN)
    Route::get('/', [NurseController::class , 'index']);
    Route::post('/', [NurseController::class , 'create']);
    Route::get('/{id}', [NurseController::class, 'read'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::put('/{id}', [NurseController::class, 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::delete('/{id}', [NurseController::class, 'delete'])->where("id" , PARAM_EXPRESSIONS["id"]);


    // Current Nurse end-points (NURSE)
    Route::get('/me' , [NurseController::class , 'me']);
    Route::put("/me" , [NurseController::class , "updateMe"]);
});


Route::group([      // patients end-points  (Policies Problem)
    'prefix' => 'patients',
    'middleware' => ['api'],
] , function($router) {

    // Create a new patient account (ANONYMOUS)
    Route::post('/', [PatientController::class , 'create']);

    // Confirm current patient account (PATIENT)
    Route::post('/confirm' , [PatientController::class , "confirm"]);

    // Patients CRUD functionality  (ADMIN)
    Route::get('/', [PatientController::class , 'index']);
    Route::get('/{id}', [PatientController::class, 'read'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::put('/{id}', [PatientController::class, 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::delete('/{id}', [PatientController::class, 'delete'])->where("id" , PARAM_EXPRESSIONS["id"]);

    // Get doctors for a specific patient (ADMIN)
    Route::get('/{id}/doctors' , [PatientController::class , "doctors"])->where("id" , PARAM_EXPRESSIONS["id"]);

    //Current Patient end-points    (PATIENT)
    Route::get("/me" ,          [PatientController::class , 'me']);
    Route::put("/me" , [PatientController::class , "updateMe"]);
    Route::get("/me/doctors" ,  [PatientController::class , 'myDoctors']);
});


Route::group([      // departements end-points
    'prefix' => 'departements',
    'middleware' => ['api'],
] , function($router) {

    // Departements CRUD functionality     (ADMIN)
    Route::get('/', [DepartementController::class , 'index']);
    Route::post('/', [DepartementController::class , 'create']);
    Route::get('/{id}', [DepartementController::class, 'read'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::put('/{id}', [DepartementController::class, 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::delete('/{id}', [DepartementController::class, 'delete'])->where("id" , PARAM_EXPRESSIONS["id"]);

    // Doctors in a specific departement   (ADMIN)
    Route::get('/{id}/doctors' , [DepartementController::class , "listDoctors"])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::post('/{id}/doctors' , [DepartementController::class , "createDoctor"])->where("id" , PARAM_EXPRESSIONS["id"]);

    // Nurses in a specific departement    (ADMIN)
    Route::get('/{id}/nurses' , [DepartementController::class , "listNurses"])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::post('/{id}/nurses' , [DepartementController::class , "createNurse"])->where("id" , PARAM_EXPRESSIONS["id"]);

    // Clinics in a specific departement    (ADMIN)
    Route::get('/{id}/clinics'  , [DepartementController::class , "listClinics"])->where("id" , PARAM_EXPRESSIONS["id"]);        // list all clinics in a specific departement
    Route::post('/{id}/clinics' , [DepartementController::class , "createClinic"])->where("id" , PARAM_EXPRESSIONS["id"]);        // add a new internal clinic
});


Route::group([          // clinics end-points
    'prefix' => 'clinics',
    'middleware' => ['api'],
], function($router) {

    // Clinics CRUD functionality (ADMIN)
    Route::get('/', [ClinicController::class , 'index']);
    Route::get('/{id}', [ClinicController::class , 'readClinic'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::post('/' , [ClinicController::class , 'createClinic']);
    Route::put('/{id}', [ClinicController::class , 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::delete('/{id}', [ClinicController::class , 'delete'])->where("id" , PARAM_EXPRESSIONS["id"]);

});


Route::group([
    "prefix" => 'appointements',    //add end-point for update appointement status
    'middleware' => ['api'],
], function ($router) {     // no deletetion for appointements

    // List all Appointements       (ADMIN)
    Route::get("/" , [AppointementController::class , 'index']);

    // List doctor's appointements  (ADMIN)
    Route::get("/doctors/{id}"  , [AppointementController::class , "listDoctorAppointements"])->where("id" , PARAM_EXPRESSIONS["id"]);

    // List patient's appointements (ADMIN)
    Route::get("/patients/{id}" , [AppointementController::class , "listPatientAppointements"])->where("id" , PARAM_EXPRESSIONS["id"]);

    // Create a new appointement    (PAITENT)
    Route::post("/" , [AppointementController::class , 'create']);

    // My Appointements (PATIENT)
    Route::get("/me" , [AppointementController::class , "me"]);
    Route::get("/me/{id}" , [AppointementController::class , "readAppointement"])->where("id" , PARAM_EXPRESSIONS["id"]);

    // My patients (DOCTOR)
    Route::get("/me/patients" , [AppointementController::class , "patients"]);
    Route::get("/me/patients/{id}" , [AppointementController::class , "readPatient"])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::put("/me/patients/{id}"  , [AppointementController::class , 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::post("/me/patients/{id}/tests"  , [AppointementController::class , 'submit'])->where("id" , PARAM_EXPRESSIONS["id"]);

    // List Appointements Schedule (PATIENT)
    Route::get("/schedule/doctors/{id}" , [AppointementController::class , 'listDoctorSchedule'])->where("id" , PARAM_EXPRESSIONS["id"]);     //patients should see that
});


Route::group([
    "middleware" => ["api"],
    "prefix" => "tests"
] , function($router) { // no deletion for routine tests

    Route::get("/" , [RoutineTestController::class , "index"]);
    Route::get("/{id}" , [RoutineTestController::class , "read"])->where("id" , PARAM_EXPRESSIONS["id"]);

    Route::get("/patients/{id}" , [RoutineTestController::class , "listPatientTests"])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::get("/doctors/{id}"  , [RoutineTestController::class , "listDoctorTests"])->where("id" , PARAM_EXPRESSIONS["id"]);

    Route::get("/me" , [RoutineTestController::class , "me"]); //for patients
    Route::get("/me/{id}" , [RoutineTestController::class , "readMyTest"])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::get("/me/patients" , [RoutineTestController::class , "myPatients"]); //for doctors
    Route::get("/me/patients/{id}" , [RoutineTestController::class , "readMyPatients"])->where("id" , PARAM_EXPRESSIONS["id"]); //for doctors
    Route::put("/me/patients/{id}" , [RoutineTestController::class , "update"])->where("id" , PARAM_EXPRESSIONS["id"]);

});

Route::group([
    "middleware" => ["api"],
    "prefix" => "verify_tokens"
] , function($router) {
    //Search on users' tokens by their SSN (ADMIN)
    Route::post("/search" , [VerifyTokenController::class , "search"]);
});
