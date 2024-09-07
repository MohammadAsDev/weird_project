<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Enums\Role;
use App\Http\Requests\PatientForm;
use App\Http\Requests\PreCheckForm;
use App\Http\Requests\UserForm;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
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

    public static function getPatientOr404($patientId) {
        $patient = Patient::where('user_id' , $patientId)->first();
        if ( $patient == null ) {
            abort(404 , "patient does not exist");       
        }
        return $patient;
    }

    private function getDoctors($patientId) {
        return Doctor::join(
            "appointements as app" , 
            "app.doctor_id" , 
            "=" , 
            "doctors.user_id"
        )->where("app.patient_id" , $patientId)->get()->unique('user_id');
    }

    /**
     *  @OA\Post(
     *      path="/api/patients",
     *      tags={"Anonymous"},
     *      operationId = "createPatient",
     *      summary = "create a new patient",
     *      description= "Create Patient Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={
     *                  "first_name",
     *                  "last_name",
     *                  "email",
     *                  "password",
     *                  "phone_number",
     *                  "address",
     *                  "gender",
     *                  "birth_date",
     *                  "ssn",
     *              },
     *              @OA\Property(property="first_name",type="string"),
     *              @OA\Property(property="last_name",type="string"),
     *              @OA\Property(property="email",type="string"),
     *              @OA\Property(property="password",type="string"),
     *              @OA\Property(property="phone_number",type="string"),
     *              @OA\Property(property="address",type="string"),
     *              @OA\Property(property="gender",type="integer"),
     *              @OA\Property(property="birth_date",type="date"),
     *              @OA\Property(property="ssn",type="string"),
     *          ),
     *      ),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *  )
     */
    protected function create(PatientForm $request) {
        
        $validated = $request->validated();
        $user_data = array_merge($validated , ["role_id" => Role::ANONYMOUS->value]);

        User::create($user_data);

        return response()->json([
            "status" => "created",
            "details" => "now go to the hospital to complete your registration",
            "result" => $validated
        ], 200);
    }

    
    /**
     *  @OA\Get(
     *      path="/api/patients/{id}",
     *      tags={"Admin"},
     *      operationId = "readPatient",
     *      summary = "read a patient",
     *      description= "Read Patient Endpoint.",
     *      @OA\Parameter(name="id", description="patient's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    protected function read($id) {
        $patient = PatientController::getPatientOr404($id);
        $this->authorize('view' , $patient);
        return response()->json(
            Controller::formatData(
                $patient , 
                PatientController::ADMIN_READ_RESPONSE_FORMAT
            ), 200
        );
    }


    /**
     *  @OA\Put(
     *      path="/api/patients/{id}",
     *      tags={"Admin"},
     *      operationId = "updatePatient",
     *      summary = "update a patient",
     *      description= "Update Patient Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="first_name",type="string"),
     *              @OA\Property(property="last_name",type="string"),
     *              @OA\Property(property="email",type="string"),
     *              @OA\Property(property="password",type="string"),
     *              @OA\Property(property="phone_number",type="string"),
     *              @OA\Property(property="address",type="string"),
     *              @OA\Property(property="gender",type="integer" , enum=App\Enums\Gender::class),
     *              @OA\Property(property="birth_date",type="date"),
     *              @OA\Property(property="ssn",type="string"),
     *          ),
     *      ),
     *      @OA\Parameter(name="id", description="patient's id" , in="path", required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="420", description="Unprocessable Content"),
     *  )
     */
    protected function update(PatientForm $request , $id) {
        $patient = PatientController::getPatientOr404($id);
        $this->authorize('update' , $patient);

        $validated = $request->validated();
        $user_data = Arr::except($validated , ['blood_type' , 'aspirin_allergy']);

        $status_code = 0;
        $response_data = [];
    
        DB::beginTransaction();
        try {
            $patient->user->update($user_data);
            $patient->update([
                "blood_type" => $validated["blood_type"] ?? $patient->blood_type,
                "aspirin_allergy" => $validated["aspirin_allergy"] ?? $patient->aspirin_allergy
            ]);
            DB::commit();

            $status_code = 200;
            $response_data = Controller::formatData(
                $patient , 
                PatientController::ADMIN_READ_RESPONSE_FORMAT
            );
        } catch (Exception $exp) {
            DB::rollBack();
            
            $status_code = 500;
        }


        return response()->json($response_data , $status_code);
    }


    /**
     *  @OA\Delete(
     *      path="/api/patients/{id}",
     *      tags={"Admin"},
     *      operationId = "deletePatient",
     *      summary = "delete a patient",
     *      description= "Delete Patient Endpoint.",
     *      @OA\Parameter(name="id", description="patient's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="204", description="No Content"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    protected function delete($id) {
        $patient = PatientController::getPatientOr404($id);
        $this->authorize('delete' , $patient);

        $user = $patient->user;
        
        $status_code = 0;
        $response_data = [];

        DB::beginTransaction();
        try {
            $patient->delete();
            $user->delete();
            DB::commit();

            $status_code = 204;
        } catch (Exception $exp) {
            DB::rollBack();
            $status_code = 500;
        }
        return response()->json($response_data, $status_code);
    }

    /**
     *  @OA\Get(
     *      path="/api/patients/{id}/doctors",
     *      tags={"Admin"},
     *      operationId = "listPatientDoctors",
     *      summary = "list doctors for a patient",
     *      @OA\Parameter(name="id", description="patient's id" , in="path" , required=true, 
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      description= "List Patient's Doctors Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    protected function doctors($id){
        $patient = PatientController::getPatientOr404($id);
        $this->authorize("viewDoctors" , $patient);

        $doctors = $this->getDoctors($id);
        return response()->json(
            $this->paginate(
                Controller::formatCollection(
                    $doctors,
                    PatientController::ADMIN_READ_RESPONSE_FORMAT
                )
            )
        );
    }


    /**
     *  @OA\Get(
     *      path="/api/patients",
     *      tags={"Admin"},
     *      operationId = "listPatients",
     *      summary = "list all patients",
     *      description= "List Patients Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    protected function index(){
        $this->authorize("viewAny" , Patient::class);

        $patients = Patient::all();
        return response()->json(
            $this->paginate(
                Controller::formatCollection(
                    $patients,
                    PatientController::ADMIN_READ_RESPONSE_FORMAT
                )
            )
        );
    }



    /**
     *  @OA\Get(
     *      path="/api/patients/prechecks",
     *      tags={"Admin"},
     *      operationId = "listPrechecks",
     *      summary = "list all uncompleted prechecks",
     *      description= "List Prechecks Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    protected function withNoPrecheck() {
        $this->authorize("viewAny" , Patient::class);

        $unregistered_patients = 
            User::where("users.role_id" , "=" , Role::ANONYMOUS->value)->get();

        return response()->json($this->paginate(
            Controller::formatCollection(
                $unregistered_patients,
                PatientController::ADMIN_PRECHECK_FORMAT
            )
        ));
    }
    
    /**
     *  @OA\Post(
     *      path="/api/patients/prechecks",
     *      tags={"Admin"},
     *      operationId = "savePrecheck",
     *      summary = "save a patient's precheck",
     *      description= "Save Patient's Precheck Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={"ssn" , "blood_type" , "aspirin_allergy"},
     *              @OA\Property(property="ssn",type="string"),
     *              @OA\Property(property="blood_type",type="integer", enum=App\Enums\BloodType::class),
     *              @OA\Property(property="aspirin_allergy",type="boolean"),
     *          ),
     *      ),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *  )
     */
    public function set_precheck(PreCheckForm $request) {
        $this->authorize("create" , Patient::class);
        $validated = $request->validated();
        $patient_user = User::where("ssn" , $validated["ssn"])->first();
        $precheck_data = Arr::except($validated , ["ssn"]);

        DB::beginTransaction();
        try {
            $patient_user->update([
                "role_id" => Role::PATIENT->value
            ]);
            Patient::create(array_merge(
                $precheck_data,
                ["user_id" => $patient_user->id]
            ));
            DB::commit();
        } catch ( Exception $exp ) {
            DB::rollBack();
        }

        return response()->json([
            "user_data" => $patient_user,
            "details" => "user has been completly registered in the system",
            "precheck_data" => $precheck_data
        ], 200);
    }


    /**
     *  @OA\Get(
     *      path="/api/patients/me",
     *      tags={"Patient"},
     *      operationId = "currentPatient",
     *      summary = "read current patient's info",
     *      description= "Current Patient Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    protected function me(Request $request) {
        $current_user = $request->user();
        $current_patient = 
            Patient::where(
                "user_id",
                $current_user->id
            )->first();
        if ( $current_patient == null ) {
            return response()->json([
                "details" => "current user is not a patient"
            ],403);
        }

        return response()->json(
            Controller::formatData(
                $current_patient,
                PatientController::ADMIN_READ_RESPONSE_FORMAT
            )
        );
    }


    /**
     *  @OA\Put(
     *       path="/api/patients/me",
     *       tags={"Patient"},
     *       operationId = "updateCurrentPatient",
     *       summary = "update personal info for current patient",
     *          @OA\RequestBody(
     *              @OA\JsonContent(
     *                  type="object",
     *                  @OA\Property(property="first_name",type="string"),
     *                  @OA\Property(property="last_name",type="string"),
     *                  @OA\Property(property="email",type="string"),
     *                  @OA\Property(property="password",type="string"),
     *                  @OA\Property(property="phone_number",type="string"),
     *                  @OA\Property(property="address",type="string"),
     *                  @OA\Property(property="gender",type="integer" ,  enum=App\Enums\Gender::class),
     *                  @OA\Property(property="birth_date",type="date"),
     *              ),
     *          ),
     *       description= "Update Patient's Personal Info Endpoint.",
     *       @OA\Response(response="200", description="OK"),
     *       @OA\Response(response="403", description="Forbidden"),
     *       @OA\Response(response="422", description="Unprocessable Content")
     *  )
     */
    public function updateMe(UserForm $request) {
        $current_user = $request->user();
        $current_patient = Patient::where(
            "user_id" , $current_user->id
        )->first();
        if ( $current_patient == null ) {
            return response()->json([
                "details" => "the current user is not a patient"
            ] , 403);
        }
        $validated = $request->validated();
        $current_user->update($validated);
        return response()->json(
            ["status" => "updated" , "data" => $validated]
        );
    }    


    /**
     *  @OA\Get(
     *      path="/api/patients/me/doctors",
     *      tags={"Patient"},
     *      operationId = "currentPatientDoctors",
     *      summary = "list current patient's doctors",
     *      description= "Current Patient's Doctors Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    protected function myDoctors(Request $request){
        $user_id = $request->user()->id;
        $patient = Patient::where("user_id" , $user_id)->first();
        if ( $patient == null ) {
            return response()->json([
                "details" => "current user is not a patient"
            ] , 403);
        }
        $doctors = $this->getDoctors($user_id);
        return response()->json(
            $this->paginate(
                Controller::formatCollection(
                    $doctors,
                    PatientController::ADMIN_READ_RESPONSE_FORMAT
                )
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
