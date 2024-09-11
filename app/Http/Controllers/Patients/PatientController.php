<?php

namespace App\Http\Controllers\Patients;

use App\Enums\AppointementStatus;
use App\Http\Controllers\Controller;
use App\Enums\Role;
use App\Enums\VerificationTokenStatus;
use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Requests\PatientForm;
use App\Http\Requests\TokenForm;
use App\Http\Requests\UserForm;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\VerifyToken;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{

    const ADMIN_READ_RESPONSE_FORMAT = [        // Doctor's View on Patient's Data
        "id" => "id",
        "first_name" => "first_name",
        "last_name" => "last_name",
        "email" => "email",
        "phone_number" => "phone_number",
        "gender" => "gender",
        "address" => "address",
        "birth_date" => "birth_date",
        "profile_picture_path" => "profile_picture_path",
        "ssn" => "ssn",
        "structured" => true
    ];

    const DOCTOR_READ_RESPONSE_FORMAT = [   // Doctor's View on Patient's Data
        "id" => "id",
        "first_name" => "first_name",
        "last_name" => "last_name",
        "gender" => "gender",
        "birth_date" => "birth_date",
        "profile_picture_path" => "profile_picture_path",
        "structured" => true
    ];


    public static function getPatientOr404($patientId) {
        $patient = User::where("role_id" , Role::PATIENT)->where('id' , $patientId)->first();
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
        )->where(
            "app.patient_id" , 
            $patientId
        )->where(
            "app.status",
            AppointementStatus::ACCEPTED
        )->get()->unique('user_id');
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
        $user_data = array_merge($validated , ["role_id" => Role::PATIENT->value]);

        $response_data = [];
        $status_code = 0;

        $token = substr(sha1($validated["email"]) , 0, 10);

        DB::beginTransaction();
        try {
            $user = User::create($user_data);
            VerifyToken::create([
                "user_id" => $user->id,
                "token" => $token,
            ]);

            DB::commit();

            $status_code = 200;
            $response_data = [
                "status" => "created",
                "details" => "now go to the hospital to complete your registration",
                "result" => $validated
            ];

        } catch (Exception $exp) {
            DB::rollBack();
            $status_code = 500;
            $response_data = [
                "status" => "uncreated"
            ];
        }


        return response()->json($response_data, $status_code);
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
     *              @OA\Property(property="gender",type="integer" , ref="#/components/schemas/Gender"),
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

        $status_code = 0;
        $response_data = [];
    
        DB::beginTransaction();
        try {
            $patient->update($validated);
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
    protected function index() {
        $this->authorize("viewAny" , Patient::class);

        $patients = User::where("role_id" , Role::PATIENT)->get();
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
     *  @OA\Post(
     *      path="/api/patients/confirm",
     *      tags={"Patient"},
     *      operationId = "confirmPatient",
     *      summary = "confirm patient's account",
     *      description= "Confirm Patient Endpoint.",
     *          @OA\RequestBody(
     *              @OA\JsonContent(
     *                  type="object",
     *                  required={"token"},
     *                  @OA\Property(property="token",type="string"),
     *              ),
     *          ),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *      @OA\Response(response="401", description="Unauthorized"),
     *      @OA\Response(response="400", description="Bad Request"),
     *  )
     */
    protected function confirm(TokenForm $request) {
        $this->authorize("confirm" , Patient::class);
        $validated = $request->validated();
        
        $current_user = $request->user();
        if($current_user == null) {
            return response()->json([
                "details" => "curret user is undefined"
            ] , 401);
        } 

        $verification_token = $current_user->verifyToken;
        if ( $verification_token->is_verified ) {
            return response()->json([
                "details" => "current user is already verified"
            ] , 400);
        } 


        $current_user_token = $verification_token->token;

        if (strcasecmp($current_user_token , $validated["token"]) != 0) {
            return response()->json([
                "details" => "invalid verification token"
            ],422);
        }

        $status_code = 0;
        $response_data = [];

        DB::beginTransaction();
        try {
            $current_user->markEmailAsVerified();
            $verification_token->update([
                "is_verified" => true
            ]);

            DB::commit();
            $status_code = 200;
            $response_data = ["status" => "verified"];
        } catch (Exception $exp) {
            DB::rollBack();

            $status_code = 500;
            $response_data = ["status" => "failed to verify"];
        }
        return response()->json($response_data , $status_code);
    }

    /**
     *  @OA\Get(
     *      path="/api/patients/me",
     *      tags={"Patient"},
     *      operationId = "currentPatient",
     *      summary = "current patient's account",
     *      description= "Current Patient Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="401", description="Unauthorized"),
     *  )
     */
    protected function me(Request $request) {
        $current_user = $request->user();
        if ( $current_user == null ) {
            return response()->json([
                "details" => "current user is undefined"
            ],401);
        }
        if ( !$current_user->hasVerifiedEmail() ) {
            return response()->json([
                "details" => "your email is not verified."
            ] , 401);
        }
        if ( $current_user->getRoleID() != Role::PATIENT ) {
            return response()->json([
                "details" => "current user is not a patient"
            ],403);
        }

        return response()->json(
            Controller::formatData(
                $current_user,
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
     *                  @OA\Property(property="gender",type="integer" ,  ref="#/components/schemas/Gender"),
     *                  @OA\Property(property="birth_date",type="date"),
     *              ),
     *          ),
     *       description= "Update Patient's Personal Info Endpoint.",
     *       @OA\Response(response="200", description="OK"),
     *       @OA\Response(response="403", description="Forbidden"),
     *       @OA\Response(response="422", description="Unprocessable Content"),
     *       @OA\Response(response="401", description="Unauthorized"),
     *  )
     */
    public function updateMe(UserForm $request) {
        $current_user = $request->user();
        if ( $current_user == null ) {
            return response()->json([
                "details" => "current user is undefined"
            ],401);
        }
        if ( !$current_user->hasVerifiedEmail() ) {
            return response()->json([
                "details" => "your email is not verified."
            ] , 401);
        }

        if ( $current_user->getRoleID() != Role::PATIENT ) {
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
     *      @OA\Response(response="401", description="Unauthorized"),
     *  )
     */
    protected function myDoctors(Request $request){
        $current_user = $request->user();
        if ( $current_user == null ) {
            return response()->json([
                "details" => "current user is undefined"
            ],401);
        }

        if ( !$current_user->hasVerifiedEmail() ) {
            return response()->json([
                "details" => "your email is not verified."
            ] , 401);
        }
        
        $doctors = $this->getDoctors($current_user->id);
        
        return response()->json(
            $this->paginate(
                Controller::formatCollection(
                    $doctors,
                    DoctorController::PATIENT_READ_RESPONSE_FORMAT
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
