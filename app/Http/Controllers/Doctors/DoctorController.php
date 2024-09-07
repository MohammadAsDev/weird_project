<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorForm;
use App\Enums\Role;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\Nurses\NurseController;
use App\Http\Requests\UserForm;
use App\Models\Doctor;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{

    public const ADMIN_INDEX_RESPONSE_FORMAT = [
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
            "structured" => false
        ],
        "departement_id" => "departement_id",
        "specialization" => "specialization",
        "short_description" => "short_description",
        "rate" => "rate",
        "structured" => true
    ];

    public const ADMIN_READ_RESPONSE_FORMAT = [
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
            "structured" => false
        ],
        
        "departement" => "departement",
        "specialization" => "specialization",
        "short_description" => "short_description",
        "rate" => "rate",
        "structured" => true
    ];

    public const PATIENT_READ_RESPONSE_FORMAT = [
        "user" => [
            "first_name" => "first_name",
            "last_name" => "last_name",
            "phone_number" => "phone_number",
            "gender" => "gender",
            "profile_picture_path" => "profile_picture_path",
            "structured" => false
        ] , 
        "specialization" => "specialization",
        "short_description" => "short_description",
        "rate" => "rate",
        "structured" => true
    ];

    public static function getDoctorOr404($doctorId) {
        $doctor = Doctor::where('user_id' , $doctorId)->first();
        if ( $doctor == null ) {
            abort(404 , "doctor does not exist"); 
        }
        return $doctor;
    }

    /**
     * @OA\Post(
     *      path="/api/doctors",
     *      operationId = "createDoctor",
     *      summary = "add new doctors to the system",
     *      description= "Create Doctor Endpoint.",
     *      tags={"Admin"},
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="422", description="Unprocessable Content"), 
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
     *                  "departement_id",
     *                  "specialization",
     *                  "short_description",
     *                  "rate"
     *              },
     *              @OA\Property(property="first_name",type="string"),
     *              @OA\Property(property="last_name",type="string"),
     *              @OA\Property(property="email",type="string"),
     *              @OA\Property(property="password",type="string"),
     *              @OA\Property(property="phone_number",type="string"),
     *              @OA\Property(property="address",type="string"),
     *              @OA\Property(property="gender",type="integer", enum=App\Enums\Gender::class),
     *              @OA\Property(property="birth_date",type="date"),
     *              @OA\Property(property="departement_id",type="integer"),
     *              @OA\Property(property="specialization",type="integer" , enum=App\Enums\MedicalSpecialization::class),
     *              @OA\Property(property="short_description",type="string"),
     *              @OA\Property(property="rate",type="integer" , enum=App\Enums\Rate::class)
     *          ),
     *      ),
     *  ),
     * )
     */
    protected function create(DoctorForm $request) {
        $this->authorize('create' , Doctor::class);
        
        $validated = $request->validated();
        $user_data = Arr::except($validated , ['specialization' , 'rate' , 'short_description']);
        $user_data = array_merge($user_data , ["role_id" => Role::DOCTOR->value]);

        DB::beginTransaction();
        $status_code = 0;
        $repsonse_data = [];
        try {
            $user = User::create($user_data);
            Doctor::create([
                'user_id' => $user->id,
                'specialization' => $validated['specialization'],
                'rate' => $validated['rate'],
                'short_description' => $validated['short_description'],
            ]);
            DB::commit();
     
            $status_code = 200;
            $repsonse_data = ["status" => "created" , "data" => $validated];
     
        } catch (Exception $exp) {
            DB::rollBack();
            $status_code = 500;
            $repsonse_data = ["status" => "uncreated"];
     
        }

        return response()->json($repsonse_data , $status_code);

    }

    
    /**
     *  @OA\Get(
     *      path="/api/doctors/{id}",
     *      tags={"Admin"},
     *      operationId = "readDoctor",
     *      summary = "read a doctor info",
     *      description= "Read a Specific Doctor Info Endpoint.",
     *      @OA\Parameter(
     *          name="id" , 
     *          description="doctor's id" , 
     *          in="path", 
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found")
     *  )
     */
    protected function read($id) {
        $doctor = DoctorController::getDoctorOr404($id);
        $this->authorize('view' , $doctor);
        return response()->json(
            Controller::formatData(
                $doctor ,
                DoctorController::ADMIN_READ_RESPONSE_FORMAT
            ),
         200);
    }

    /**
     *  @OA\Put(
     *      path="/api/doctors/{id}",
     *      tags={"Admin"},
     *      operationId = "updateDoctor",
     *      summary = "update doctor info",
     *      description= "Update Specific Doctor Info Endpoint.",
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
     *              @OA\Property(property="departement_id",type="integer"),
     *              @OA\Property(property="specialization",type="integer" , enum=App\Enums\MedicalSpecialization::class),
     *              @OA\Property(property="short_description",type="string"),
     *              @OA\Property(property="rate",type="integer" , enum=App\Enums\Rate::class)
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="id" , 
     *          description="doctor's id" , 
     *          in="path" , 
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="422", description="Unprocessable Content")
     *  )
     */
    protected function update(DoctorForm $request , $id) {
        $doctor = DoctorController::getDoctorOr404($id);
        $this->authorize('update' , $doctor);

        $validated = $request->validated();
        $user_data = Arr::except($validated , ['specialization' , 'rate' , 'short_description']);

        $status_code = 0;
        $response_data = [];

        DB::beginTransaction();
        try {
            $doctor->user->update($user_data);
            $doctor->update([
                "specialization" => $validated['specialization'] ?? $doctor->specialization,
                "rate" => $validated["rate"] ?? $doctor->rate,
                "short_description" => $validated["short_description"] ?? $doctor->short_description,
                "departement_id" => $validated["departement_id"] ?? $doctor->departement_id
            ]);
            DB::commit();

            $status_code = 200;
            $response_data = Controller::formatData(
                $doctor ,
                DoctorController::ADMIN_READ_RESPONSE_FORMAT
            );
        } catch (Exception $exp) {
            DB::rollBack();
            $status_code = 500;
        }
        
        return response()->json($response_data , $status_code);
    }

    /**
     *  @OA\Delete(
     *      path="/api/doctors/{id}",
     *      tags={"Admin"},
     *      operationId = "deleteDoctor",
     *      summary = "delete doctor info",
     *      description= "Delete Specific Doctor Endpoint.",
     *      @OA\Parameter(name="id" , description="doctor's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="204", description="No Content"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found")
     *  )
     */
    protected function delete($id) {
        $doctor = DoctorController::getDoctorOr404($id);
        $this->authorize('delete' , $doctor);

        $status_code = 0;
        $response_data = [];

        DB::beginTransaction();
        try {
            $user = $doctor->user;
            $doctor->delete();
            $user->delete();          
            DB::commit();

            $status_code = 204;
        } catch ( Exception $exp ) {
            DB::rollBack();

            $status_code = 500;
        }
        
        return response()->json($response_data , $status_code);
    }

    /**
     *  @OA\Get(
     *      path="/api/doctors/",
     *      tags={"Admin"},
     *      operationId = "List Doctors",
     *      summary = "list all doctors",
     *      description= "List All Doctors Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden")
     *  )
     */
    protected function index(){
        $this->authorize("viewAny" , Doctor::class);
        $doctors = Doctor::all();
        return response()->json($this->paginate(
            Controller::formatCollection(
                $doctors,
                DoctorController::ADMIN_INDEX_RESPONSE_FORMAT
            )
        ));
    }

    /**
     *  @OA\Get(
     *      path="/api/doctors/{id}/nurses",
     *      tags={"Admin"},
     *      operationId = "listDoctorNurses",
     *      summary = "list doctor's nurses",
     *      description= "List Specific Doctor's Nurses Endpoint.",
     *      @OA\Parameter(name="id" , description="doctor's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="403", description="Forbidden")
     *  )
     */
    protected function nurses($id) {
        $doctor = DoctorController::getDoctorOr404($id);
        $this->authorize("viewNurses", $doctor);
        $nurses = $doctor->nurses;
        return response()->json($this->paginate(
            Controller::formatCollection(
                $nurses,
                NurseController::ADMIN_INDEX_RESPONSE_FORMAT
            )
        ));
    }

    /**
     *  @OA\Get(
     *       path="/api/doctors/{id}/clinics",
     *       tags={"Admin"},
     *       operationId = "listDoctorClinics",
     *       summary = "list doctor's clinics",
     *       description= "List Specific Doctor's Clinics Endpoint.",
     *       @OA\Parameter(name="id" , description="doctor's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *       @OA\Response(response="200", description="OK"),
     *       @OA\Response(response="404", description="Object Not Found"),
     *       @OA\Response(response="403", description="Forbidden")
     *  )
     */
    protected function clinics($id) {
        $doctor = DoctorController::getDoctorOr404($id);
        $this->authorize("viewClinics" , $doctor);
        $clinics = $doctor->clinics;
        return response()->json($this->paginate(
            Controller::formatCollection(
                $clinics,
                ClinicController::PATIENT_CLINIC_ONLY_RESPONSE_FOMAT
            )
        ));
    }


    /**
     *  @OA\Get(
     *       path="/api/doctors/me",
     *       tags={"Doctor"},
     *       operationId = "currentDoctor",
     *       summary = "current doctor info",
     *       description= "Current Doctor Endpoint.",
     *       @OA\Response(response="200", description="OK"),
     *       @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function me(Request $request) {
        $current_user = $request->user();
        $current_doctor = Doctor::where(
            "user_id" , $current_user->id
        )->first();
        if ( $current_doctor == null ) {
            return response()->json([
                "details" => "the current user is not a doctor"
            ] , 403);
        }
        return response()->json(
            Controller::formatData(
                $current_doctor,
                DoctorController::ADMIN_READ_RESPONSE_FORMAT
            )
        );
    }



    /**
     *  @OA\Put(
     *       path="/api/doctors/me",
     *       tags={"Doctor"},
     *       operationId = "updateCurrentDoctor",
     *       summary = "update personal info for current doctor",
     *          @OA\RequestBody(
     *              @OA\JsonContent(
     *                  type="object",
     *                  @OA\Property(property="first_name",type="string"),
     *                  @OA\Property(property="last_name",type="string"),
     *                  @OA\Property(property="email",type="string"),
     *                  @OA\Property(property="password",type="string"),
     *                  @OA\Property(property="phone_number",type="string"),
     *                  @OA\Property(property="address",type="string"),
     *                  @OA\Property(property="gender",type="integer" , enum=App\Enums\Gender::class),
     *                  @OA\Property(property="birth_date",type="date"),
     *              ),
     *          ),
     *       description= "Update Doctor's Personal Info Endpoint.",
     *       @OA\Response(response="200", description="OK"),
     *       @OA\Response(response="403", description="Forbidden"),
     *       @OA\Response(response="422", description="Unprocessable Content")
     *  )
     */
    public function updateMe(UserForm $request) {
        $current_user = $request->user();
        $current_doctor = Doctor::where(
            "user_id" , $current_user->id
        )->first();
        if ( $current_doctor == null ) {
            return response()->json([
                "details" => "the current user is not a doctor"
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
     *      path="/api/doctors/me/nurses",
     *      tags={"Doctor"},
     *      operationId = "listMyNurses",
     *      summary = "list current doctor's nurse info",
     *      description= "List Nurses Working With The Current Doctor.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function myNurses(Request $request) {
        $current_user = $request->user();
        $current_doctor = Doctor::where(
            "user_id" , $current_user->id
        )->first();
        if ( $current_doctor == null ) {
            return response()->json([
                "details" => "the current user is not a doctor"
            ] , 403);
        }
        $nurses = $current_doctor->nurses;
       return response()->json($this->paginate(
            Controller::formatCollection(
                $nurses,
                NurseController::PATIENT_READ_RESPONSE_FORMAT
            )
       ));
    }


    /**
     *  @OA\Get(
     *      path="/api/doctors/me/clinics",
     *      tags={"Doctor"},
     *      operationId = "listMyClinics",
     *      summary = "list current doctor's clinic info",
     *      description= "List Clinics For The Current Doctor Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function myClinics(Request $request) {
        $current_user = $request->user();
        $current_doctor = Doctor::where(
            "user_id" , $current_user->id
        )->first();
        if ( $current_doctor == null ) {
            return response()->json([
                "details" => "the current user is not a doctor"
            ] , 403);
        }
        $clinics = $current_doctor->clinics;
       return response()->json($this->paginate(
            Controller::formatCollection(
                $clinics,
                ClinicController::PATIENT_CLINIC_ONLY_RESPONSE_FOMAT
            )
       ));
    }

    public function paginate($items, $perPage = 5, $page = null, $options = []) {

        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);

    }
}
