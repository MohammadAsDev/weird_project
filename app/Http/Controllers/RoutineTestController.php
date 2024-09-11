<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Controllers\Patients\PatientController;
use App\Http\Requests\RoutineTestForm;
use App\Models\Doctor;
use App\Models\RoutineTest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class RoutineTestController extends Controller
{
  
    public const ADMIN_INDEX_RESPONSE_FORMAT = [
        "id" => "id",
        "patient_id" => "patient_id",
        "doctor_id" => "doctor_id",
        "breathing_rate" => "breathing_rate",
        "body_temperature" => "body_temperature",
        "pulse_rate" => "pulse_rate",
        "medical_notes" => "medical_notes",
        "prescription" => "prescription",
        "created_at" => "created_at"
    ];


    public const PATIENT_INDEX_RESPONSE_FORMAT = [
        "id" => "id",
        "doctor_id" => "doctor_id",
        "breathing_rate" => "breathing_rate",
        "body_temperature" => "body_temperature",
        "pulse_rate" => "pulse_rate",
        "medical_notes" => "medical_notes",
        "prescription" => "prescription",
        "created_at" => "created_at"
    ];


    public const DOCTOR_INDEX_RESPONSE_FORMAT = [
        "id" => "id",
        "doctor_id" => "doctor_id",
        "breathing_rate" => "breathing_rate",
        "body_temperature" => "body_temperature",
        "pulse_rate" => "pulse_rate",
        "medical_notes" => "medical_notes",
        "prescription" => "prescription",
        "created_at" => "created_at"
    ];


    public const ADMIN_READ_RESPONSE_FORMAT = [     // Admin's View
        "patient" => PatientController::ADMIN_READ_RESPONSE_FORMAT,
        "doctor" => DoctorController::ADMIN_READ_DOCTOR_ONLY_FORMAT,
        "breathing_rate" => "breathing_rate",
        "body_temperature" => "body_temperature",
        "pulse_rate" => "pulse_rate",
        "medical_notes" => "medical_notes",
        "prescription" => "prescription",
        "created_at" => "created_at"
    ];

    public const PATEINT_READ_RESPONSE_FORMAT = [   // Patient's View
        "doctor" => DoctorController::PATIENT_READ_RESPONSE_FORMAT,
        "breathing_rate" => "breathing_rate",
        "body_temperature" => "body_temperature",
        "pulse_rate" => "pulse_rate",
        "medical_notes" => "medical_notes",
        "prescription" => "prescription",
        "created_at" => "created_at"
    ];

    public const DOCTOR_READ_RESPONSE_FORMAT = [    // Doctor's View
        "patient" => PatientController::DOCTOR_READ_RESPONSE_FORMAT,
        "breathing_rate" => "breathing_rate",
        "body_temperature" => "body_temperature",
        "pulse_rate" => "pulse_rate",
        "medical_notes" => "medical_notes",
        "prescription" => "prescription",
        "created_at" => "created_at"
    ];

    public static function getTestOr404($testId) {
        $test = RoutineTest::where(
            "id", $testId
        )->first();
        if ( $test == null ){
            abort(404 , "test does not exist");
        }
        return $test;
    }


    /**
     *  @OA\Get(
     *      path="/api/tests/",
     *      tags={"Admin"},
     *      operationId = "listRoutineTests",
     *      summary = "list routine tests",
     *      description= "List Routine Tests Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function index()
    {
        $this->authorize("viewAny" , RoutineTest::class);
        $tests = RoutineTest::all();
        
        return response()->json(
            $this->paginate(Controller::formatCollection(
                $tests,
                RoutineTestController::ADMIN_INDEX_RESPONSE_FORMAT
            )
        ));
    }

    
    /**
     *  @OA\Get(
     *      path="/api/tests/{id}/",
     *      tags={"Admin"},
     *      operationId = "readRoutineTest",
     *      summary = "read a routine test",
     *      description= "Read Routine Test Endpoint.",
     *      @OA\Parameter(name="id", description="test's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    public function read($id)
    {
        $test = RoutineTestController::getTestOr404($id);
        $this->authorize("view" , $test);

        return response()->json(
            Controller::formatData(
                $test,
                RoutineTestController::ADMIN_READ_RESPONSE_FORMAT       
            )
        );
    }


    /**
     *  @OA\Get(
     *      path="/api/tests/patients/{id}/",
     *      tags={"Admin"},
     *      operationId = "listPatientTests",
     *      summary = "list patient's routine tests",
     *      description= "List Routine Tests for Specific Patient Endpoint.",
     *      @OA\Parameter(name="id", description="patient's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    public function listPatientTests($id)
    {
        $patient = PatientController::getPatientOr404($id);
        $this->authorize("viewAny" , $patient);

        $tests = $patient->tests;
        
        return response()->json($this->paginate(
            Controller::formatCollection(
                $tests,
                RoutineTestController::PATIENT_INDEX_RESPONSE_FORMAT
            )
        ));
    }


    /**
     *  @OA\Get(
     *      path="/api/tests/doctors/{id}/",
     *      tags={"Admin"},
     *      operationId = "listDoctorTests",
     *      summary = "list all doctor's routine tests",
     *      description= "List Routine Tests Made By Speicifc Doctor Endpoint.",
     *     @OA\Parameter(name="id", description="departement's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    public function listDoctorTests($id)
    {
        $doctor = DoctorController::getDoctorOr404($id);
        $this->authorize("viewAny" , $doctor);

        $tests = $doctor->tests;
        
        return response()->json($this->paginate(
            Controller::formatCollection(
                $tests,
                RoutineTestController::DOCTOR_INDEX_RESPONSE_FORMAT
            )
        ));
    }

    /**
     *  @OA\Get(
     *      path="/api/tests/me/",
     *      tags={"Patient"},
     *      operationId = "listCurrentPatientTests",
     *      summary = "list current patient's routine tests",
     *      description= "List Current Patient's Tests Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="401", description="Unauthorized"),
     *  )
     */
    public function me(Request $request) {
        $current_user = $request->user();
        if ( $current_user == null ) {
            return response()->json([
                "details" => "current user is undefined"
            ],401);
        }

        $patient = User::where("id" , $current_user->id)->first();
        if ( $patient == null ) {
            return response()->json([
                "details" => "current user is not a patient"
            ],403);
        }

        $tests = $patient->tests;
        return response()->json($this->paginate(
            Controller::formatCollection(
                $tests,
                RoutineTestController::PATIENT_INDEX_RESPONSE_FORMAT
            )
        ));
    }    


    /**
     *  @OA\Get(
     *      path="/api/tests/me/{id}/",
     *      tags={"Patient"},
     *      operationId = "readCurrentPatientTest",
     *      summary = "read current patient's routine test",
     *      description= "Read Current Patient's Test Endpoint.",
     *      @OA\Parameter(name="id", description="test's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="Object Not Found"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *  )
     */
    public function readMyTest(Request $request , $id){
        $current_user = $request->user();
        if ( $current_user == null ) {
            return response()->json([
                "details" => "current user is undefined"
            ],401);
        }
        
        $test = RoutineTest::where(
            "patient_id" , 
            $current_user->id
        )->where("id",$id)->first();
        if ( $test == null ) {
            return response()->json([
                "details" => "selected test is not in your tests"
            ],403);
        }
        return response()->json(
            Controller::formatData(
                $test,
                RoutineTestController::PATEINT_READ_RESPONSE_FORMAT
            )
        );
    }

    /**
     *  @OA\Get(
     *      path="/api/tests/me/patients/",
     *      tags={"Doctor"},
     *      operationId = "listCurrentDoctorTests",
     *      summary = "list current doctor's routine tests",
     *      description= "List Routine Tests Made By Current Doctor Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Not authorized"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *  )
     */
    public function myPatients(Request $request) {
        $current_user = $request->user();
        if ( $current_user == null ) {
            return response()->json([
                "details" => "current user is undefined"
            ],401);
        }
        
        $doctor = Doctor::where("user_id" , $current_user->id)->first();
        if ( $doctor == null ) {
            return response()->json([
                "details" => "current user is not a patient"
            ],403);
        }

        $tests = $doctor->tests;
        return response()->json($this->paginate(
            Controller::formatCollection(
                $tests,
                RoutineTestController::DOCTOR_INDEX_RESPONSE_FORMAT
            )
        ));
    }


    /**
     *  @OA\Put(
     *      path="/api/tests/me/patients/{id}/",
     *      tags={"Doctor"},
     *      operationId = "updateRoutineTest",
     *      summary = "update routine test",
     *      description= "Update Routine Test Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="breathing_rate",type="number"),
     *              @OA\Property(property="body_temperature",type="number"),
     *              @OA\Property(property="pulse_rate",type="number"),
     *              @OA\Property(property="medical_notes",type="string"),
     *              @OA\Property(property="prescription",type="string"),
     *          ),
     *     ),
     *     @OA\Parameter(name="id", description="test's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Object Not Found"),
     *     @OA\Response(response="422", description="Unprocessable Content"),
     *  )
     */
    public function update(RoutineTestForm $request , $id)
    {
        $test = RoutineTestController::getTestOr404($id);
        $this->authorize("update" , $test);

        $validated = $request->validated();
        
        $test->update($validated);
        return response()->json([
            "status" => "updated",
            "data" => $test
        ] , 200);
    }



    /**
     *  @OA\Get(
     *      path="/api/tests/me/patients/{id}/",
     *      tags={"Doctor"},
     *      operationId = "readCurrentDoctorTest",
     *      summary = "read routine test made by current doctor",
     *      description= "Read Current Doctor Test Endpoint.",
     *      @OA\Parameter(name="id", description="test's id" , in="path" , required=true,
     *           @OA\Schema(
     *               type="integer"
     *           )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="401", description="Unauthorized"),
     *  )
     */
    public function readMyPatients(Request $request, $id) {
        $current_user = $request->user();
        if ( $current_user == null ) {
            return response()->json([
                "details" => "current user is undefined"
            ],401);
        }
        
        $test = RoutineTest::where(
            "doctor_id" , $current_user->id
        )->where("id", $id)->first();
        if ( $test == null ) {
            return response()->json([
                "details" => "selected test is not in your tests"
            ],403);
        }

        return response()->json(
            Controller::formatData(
                $test,
                RoutineTestController::DOCTOR_READ_RESPONSE_FORMAT
            )
        );
    }


    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {

        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);

    }
}
