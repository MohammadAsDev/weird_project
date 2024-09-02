<?php

namespace App\Http\Controllers\Patients;

use App\Enums\BloodType;
use App\Http\Controllers\Controller;
use App\Enums\Role;
use App\Http\Requests\PatientForm;
use App\Http\Requests\PreCheckForm;
use App\Models\Patient;
use App\Models\User;
use Error;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{

    const ADMIN_READ_RESPONSE_FORMAT = [
        "user" => [
            "id" => "id",
            "first_name" => "first_name",
            "last_name" => "last_name",
            "email" => "email",
            "phone_number" => "phone_number",
            "gender" => "gender",
            "address" => "address",
            "birth_date" => "birth_date",
            "profile_picture_path" => "profile_picture_path",
        ],
      
        // Patient Info
        "aspirin_allergy" => "aspirin_allergy",
        "blood_type" => "blood_type",
        "structured" => true
    ];

    const DOCTOR_READ_RESPONSE_FORMAT = [
        "user" => [
            "first_name" => "first_name",
            "last_name" => "last_name",
            "gender" => "gender",
            "birth_date" => "birth_date",
            "profile_picture_path" => "profile_picture_path",
            "strucutred" => false,
        ],
        "aspirin_allergy" => "aspirin_allergy",
        "blood_type" => "blood_type",
        "structured" => true
    ];

    const ADMIN_PRECHECK_FORMAT = [
        "first_name" => "first_name",
        "last_name" => "last_name",
        "email" => "email",
        "phone_number" => "phone_number",
        "gender" => "gender",
        "birth_date" => "birth_date",
        "address" => "address",
        "ssn" => "ssn",
        "structured" => true
    ];

    /**
     * @OA\Post(
     *     path="/api/patients",
     *     tags={"Anonymous"},
     *     @OA\Response(response="200", description="Create new patient"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="422", description="Invalid data"),
     * )
     */
    protected function create(PatientForm $request) {
        
        $validated = $request->validated();
        $user_data = array_merge($validated , ["role_id" => Role::PATIENT->value]);

        User::create($user_data);

        return response()->json([
            "status" => "created",
            "details" => "now go to the hospital to complete your registration",
            "result" => $validated
        ], 200);
    }

    
    /**
     * @OA\Get(
     *     path="/api/patients/{id}",
     *     tags={"Admin"},
     *     @OA\Parameter(name="id", description="patient's id" , in="path"),
     *     @OA\Response(response="200", description="Read a specifc patient"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Patient does not exist"),
     * )
     */
    protected function read($id) {
        $patient = Patient::where('user_id' , $id)->first();
        if ( $patient == null ) {
            return response()->json([
                "details" => "patient account does not exist"
            ],400);       
        }
        $this->authorize('view' , $patient);
        return response()->json(
            Controller::formatData(
                $patient , 
                PatientController::ADMIN_READ_RESPONSE_FORMAT
            ), 200
        );
    }


    /**
     * @OA\Put(
     *     path="/api/patients/{id}",
     *     tags={"Admin"},
     *     @OA\Parameter(name="id", description="patient's id" , in="path"),
     *     @OA\Response(response="200", description="Update a specifc patient"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Patient does not exist"),
     *     @OA\Response(response="420", description="Invalid data"),
     * )
     */
    protected function update(PatientForm $request , $id) {
        $patient = Patient::where('user_id' , $id)->first();
        if ( $patient == null ){
            return response()->json([
                "details" => "patient account does not exist"
            ],400); 
        }
        $this->authorize('update' , $patient);

        $validated = $request->validated();
        $user_data = Arr::except($validated , ['blood_type' , 'aspirin_allergy']);

        $patient->user->update($user_data);
        $patient->update([
            "blood_type" => $validated["blood_type"] ?? $patient->blood_type,
            "aspirin_allergy" => $validated["aspirin_allergy"] ?? $patient->aspirin_allergy
        ]);


        return response()->json(
            Controller::formatData(
                $patient , 
                PatientController::ADMIN_READ_RESPONSE_FORMAT
            ), 200
        );
    }


    /**
     * @OA\Delete(
     *     path="/api/patients/{id}",
     *     tags={"Admin"},
     *     @OA\Parameter(name="id", description="patient's id" , in="path"),
     *     @OA\Response(response="204", description="Delete a specifc patient"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Patient does not exist"),
     * )
     */
    protected function delete($id) {
        $patient = Patient::where('user_id' , $id)->first();
        if ( $patient == null) {
            return response()->json([
                "details" => "patient does not exist"
            ],404);
        }
        $this->authorize('delete' , $patient);

        $user = $patient->user;
        $patient->delete();
        $user->delete();
        return response()->json([], 204);
    }


    /**
     * @OA\Get(
     *     path="/api/patients",
     *     tags={"Admin"},
     *     @OA\Response(response="200", description="List all patients"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    protected function index(){
        $this->authorize("viewAny" , Patient::class);

        $patients = Patient::all();
        $patients_response = [];

        foreach($patients as $patient) {
            $patient_data = Controller::formatData($patient , PatientController::ADMIN_READ_RESPONSE_FORMAT);
            array_push($patients_response, $patient_data);
        }
        
        return response()->json(
            $this->paginate($patients_response)
        );
    }


    /**
     * @OA\Get(
     *     path="/api/patients/{id}/appointements",
     *     tags={"Patient" , "Admin"},
     *     @OA\Parameter(name="id", description="patient's id" , in="path"),
     *     @OA\Response(response="200", description="List all appointements for a patient"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="422", description="Invalid data"),
     * )
     */
    public function appointements($id) {
        $patient = Patient::where("user_id" , $id)->first();
        if ( $patient == null ) {
            return response()->json([
                "details" => "patient does not exist"
            ] , 404);
        }
        $this->authorize("viewAppointements" , $patient);
        $appointements = $patient->appointements;
        return response()->json($this->paginate($appointements));
    }


    /**
     * @OA\Get(
     *     path="/api/patients/prechecks",
     *     tags={"Admin"},
     *     @OA\Response(response="200", description="List all patients without precheck"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    protected function withNoPrecheck() {
        $this->authorize("viewAny" , Patient::class);

        $unregistered_patients = 
        User::where("users.role_id" , "=" , Role::PATIENT->value)->
        whereNotIn("id" , function($query) {
            $query->select("user_id")->from("patients")->get();
        })->get();


        $patients_data = [];
        foreach ($unregistered_patients as $patient) {
            array_push(
                $patients_data , 
                Controller::formatData(
                    $patient , 
                    PatientController::ADMIN_PRECHECK_FORMAT
                )
            );
        }

        return response()->json(
            $this->paginate($patients_data)
        );
    }



    /**
     * @OA\Post(
     *     path="/api/patients/prechecks",
     *     tags={"Admin"},
     *     @OA\Response(response="200", description="Create a precheck for a patient"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="422", description="Invalid data"),
     * )
     */
    public function set_precheck(PreCheckForm $request) {
        $this->authorize("create" , Patient::class);
        $validated = $request->validated();
        $patient_user = User::where("ssn" , $validated["ssn"])->first();
        $precheck_data = Arr::except($validated , ["ssn"]);
        Patient::create(array_merge(
            $precheck_data,
            ["user_id" => $patient_user->id]
        ));
        return response()->json([
            "user_data" => $patient_user,
            "details" => "user has been completly registered in the system",
            "precheck_data" => $precheck_data
        ], 200);
    }

    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
