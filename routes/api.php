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
    Route::get('/{id}/nurses', [DoctorController::class, 'nurses'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::get('/{id}/clinics' , [DoctorController::class , "clinics"])->where("id" , PARAM_EXPRESSIONS["id"]);


    // Current Doctor end-points
    Route::get("/me" , [DoctorController::class , "me"]);
    Route::put("/me" , [DoctorController::class , "updateMe"]);
    Route::get("/me/nurses" , [DoctorController::class , "myNurses"]);
    Route::get("/me/clinics" , [DoctorController::class , "myClinics"]);
        
});


Route::group([      // nurses end-points
    'prefix' => 'nurses',
    'middleware' => 'api',
] , function($router) {

    // Nurses can be created only via the departements
    // Unlike doctors, nurses can't exist without departement.

    // Route::post('/', [NurseController::class , 'create']);

    // Nurses CRUD functionality
    Route::get('/', [NurseController::class , 'index']);
    Route::get('/{id}', [NurseController::class, 'read'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::put('/{id}', [NurseController::class, 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::delete('/{id}', [NurseController::class, 'delete'])->where("id" , PARAM_EXPRESSIONS["id"]);
    
    Route::get('/{id}/doctor', [NurseController::class, 'doctor'])->where("id" , PARAM_EXPRESSIONS["id"]);


    Route::get('/me' , [NurseController::class , 'me']);
    Route::put("/me" , [NurseController::class , "updateMe"]);
    Route::get('/me/doctor' , [NurseController::class , 'myDoctor']);
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


    Route::get('/{id}/doctors' , [PatientController::class , "doctors"])->where("id" , PARAM_EXPRESSIONS["id"]);

    
    // Prechecks
    Route::get('/prechecks' , [PatientController::class , 'withNoPrecheck']);
    Route::post('/prechecks' , [PatientController::class, 'set_precheck']);


    //Current Patient end-points
    Route::get("/me" ,          [PatientController::class , 'me']);
    Route::put("/me" , [PatientController::class , "updateMe"]);
    Route::get("/me/doctors" ,  [PatientController::class , 'myDoctors']);
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

    Route::get('/{id}/clinics'  , [DepartementController::class , "listClinics"])->where("id" , PARAM_EXPRESSIONS["id"]);        // list all clinics in a specific departement
    Route::post('/{id}/clinics' , [DepartementController::class , "createClinic"])->where("id" , PARAM_EXPRESSIONS["id"]);        // add a new internal clinic
});


Route::group([          // clinics end-points
    'prefix' => 'clinics',
    'middleware' => 'api',
], function($router) {

    // Clinics CRUD functionality (ADMIN)
    Route::get('/', [ClinicController::class , 'index']);
    Route::post('/' , [ClinicController::class , 'createExternalClinic']);  // create only external clinics (not sure about it)
    Route::put('/{id}', [ClinicController::class , 'update'])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::delete('/{id}', [ClinicController::class , 'delete'])->where("id" , PARAM_EXPRESSIONS["id"]);

    // Interal clinics (PATIENTS)
    Route::get('/internal' , [ClinicController::class , 'listInternalClinics']);        // patients should see that
    Route::get('/internal/{id}', [ClinicController::class , 'readInternalClinic'])->where("id" , PARAM_EXPRESSIONS["id"]);

    // External clinics (PATIENTS)
    Route::get('/external' , [ClinicController::class , 'listExternalClinics']);        // patients should see that
    Route::get('/external/{id}', [ClinicController::class , 'readExternalClinic'])->where("id" , PARAM_EXPRESSIONS["id"]);
    
});


Route::group([
    "prefix" => 'appointements',
    'middleware' => 'api',
], function ($router) {     // no deletetion for appointements
    
    // Appointements (only admins)
    Route::get("/" , [AppointementController::class , 'index']);
    Route::get("/doctors/{id}"  , [AppointementController::class , "listDoctorAppointements"])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::get("/patients/{id}" , [AppointementController::class , "listPatientAppointements"])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::get("/clinics/{id}"  , [AppointementController::class , "listClinicAppointements"])->where("id" , PARAM_EXPRESSIONS["id"]);

    Route::post("/" , [AppointementController::class , 'create']);  // for patients

    // My Appointements (for patients)
    Route::get("/me" , [AppointementController::class , "me"]);
    Route::get("/me/{id}" , [AppointementController::class , "readAppointement"])->where("id" , PARAM_EXPRESSIONS["id"]);
   
    // My patients (for doctors)
    Route::get("/me/patients" , [AppointementController::class , "patients"]);
    Route::get("/me/patients/{id}" , [AppointementController::class , "readPatient"])->where("id" , PARAM_EXPRESSIONS["id"]);
    
    // only doctors can do that
    Route::put("/me/patients/{id}"  , [AppointementController::class , 'submit'])->where("id" , PARAM_EXPRESSIONS["id"]);      

    // Appointements depending on clinics or doctors (period as query)
    Route::get("/schedule/clinics/{id}" , [AppointementController::class , 'listClinicSchedule'])->where("id" , PARAM_EXPRESSIONS["id"]);     //patients should see that
    Route::get("/schedule/doctors/{id}" , [AppointementController::class , 'listDoctorSchedule'])->where("id" , PARAM_EXPRESSIONS["id"]);     //patients should see that  
});

Route::group([
    "middleware" => "api",
    "prefix" => "tests"
] , function($router) { // no deletion for routine tests
    Route::get("/" , [RoutineTestController::class , "index"]);
    Route::get("/{id}" , [RoutineTestController::class , "read"])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::put("/{id}" , [RoutineTestController::class , "update"])->where("id" , PARAM_EXPRESSIONS["id"]);
    
    Route::get("/patients/{id}" , [RoutineTestController::class , "listPatientTests"])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::get("/doctors/{id}"  , [RoutineTestController::class , "listDoctorTests"])->where("id" , PARAM_EXPRESSIONS["id"]);
    
    Route::get("/me" , [RoutineTestController::class , "me"]); //for patients
    Route::get("/me/{id}" , [RoutineTestController::class , "readMyTest"])->where("id" , PARAM_EXPRESSIONS["id"]);
    Route::get("/me/patients" , [RoutineTestController::class , "myPatients"]); //for doctors
    Route::get("/me/patients/{id}" , [RoutineTestController::class , "readMyPatients"])->where("id" , PARAM_EXPRESSIONS["id"]); //for doctors
});