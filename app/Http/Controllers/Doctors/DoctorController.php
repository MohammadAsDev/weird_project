<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorForm;
use App\Enums\Role;
use App\Http\Controllers\DepartementController;
use App\Http\Requests\UserForm;
use App\Models\Doctor;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DoctorController extends Controller
{
    public const DOCTOR_RESOURCES = Controller::APP_URL . "api/doctors/";

    public const ADMIN_INDEX_RESPONSE_FORMAT = [        // Admin's view on doctors index
        "user" => [
            "url" => [
                "attr" => "id",
                "meta" => true,
                "prefix" => DoctorController::DOCTOR_RESOURCES
            ],
            "id" => "id",
            "first_name" => "first_name",
            "last_name" => "last_name",
            "email" => "email",
            "phone_number" => "phone_number",
            "gender" => "gender",
            "address" => "address",
            "birth_date" => "birth_date",
            "profile_picture_path" => [
                "meta" => true,
                "attr" => "profile_picture",
                "prefix" => Controller::STORAGE_URL
            ],
            "structured" => false
        ],
        
        "departement" => [
            "attr" => "departement_id",
            "meta" => true,
            "prefix" => DepartementController::DEPARTEMENTS_RESOURCES
        ],

        "specialization" => "specialization",
        "short_description" => "short_description",
        "rate" => "rate",
        'assigned_at' => 'assigned_at',
        "structured" => true
    ];

    public const PATIENT_INDEX_RESPONSE_FORMAT = [      // Patient's view on doctors index
        "user" => [
            "url" => [
                "attr" => "id",
                "meta" => true,
                "prefix" => DoctorController::DOCTOR_RESOURCES
            ],
            "id" => "id",
            "first_name" => "first_name",
            "last_name" => "last_name",
            "email" => "email",
            "phone_number" => "phone_number",
            "gender" => "gender",
            "birth_date" => "birth_date",
            "profile_picture_path" => [
                "meta" => true,
                "attr" => "profile_picture",
                "prefix" => Controller::STORAGE_URL
            ],
            "structured" => false
        ],
        "specialization" => "specialization",
        "short_description" => "short_description",
        "rate" => "rate",
        "structured" => true
    ];

    public const ADMIN_READ_RESPONSE_FORMAT = [     // Admin's View on Doctor's Data
        "user" => [
            "first_name" => "first_name",
            "last_name" => "last_name",
            "email" => "email",
            "phone_number" => "phone_number",
            "gender" => "gender",
            "address" => "address",
            "birth_date" => "birth_date",
            "profile_picture_path" => [
                "meta" => true,
                "attr" => "profile_picture",
                "prefix" => Controller::STORAGE_URL
            ],
            "url" => [
                "meta" => true,
                "attr" => "id",
                "prefix" => DoctorController::DOCTOR_RESOURCES
            ],
            "structured" => false
        ],
        "departement" => DepartementController::ALL_DEPARTEMENT_RESPONSE_FORMAT,
        "specialization" => "specialization",
        "short_description" => "short_description",
        "rate" => "rate",
        'assigned_at' => 'assigned_at',
        "structured" => true
    ];

    public const ADMIN_READ_DOCTOR_ONLY_FORMAT = [     // Admin's View on Doctor's Data
        "user" => [
            "first_name" => "first_name",
            "last_name" => "last_name",
            "email" => "email",
            "phone_number" => "phone_number",
            "gender" => "gender",
            "address" => "address",
            "birth_date" => "birth_date",
            "profile_picture_path" => [
                "meta" => true,
                "attr" => "profile_picture",
                "prefix" => Controller::STORAGE_URL
            ],
            "url" => [
                "meta" => true,
                "attr" => "id",
                "prefix" => DoctorController::DOCTOR_RESOURCES
            ],
            "structured" => false
        ],
        "specialization" => "specialization",
        "short_description" => "short_description",
        "rate" => "rate",
        'assigned_at' => 'assigned_at',
        "structured" => true
    ];

    public const DOCTOR_READ_RESPONSE_FORMAT = [     // Admin's View on Doctor's Data
        "user" => [
            "first_name" => "first_name",
            "last_name" => "last_name",
            "email" => "email",
            "phone_number" => "phone_number",
            "gender" => "gender",
            "address" => "address",
            "birth_date" => "birth_date",
            "profile_picture_path" => [
                "meta" => true,
                "attr" => "profile_picture",
                "prefix" => Controller::STORAGE_URL
            ],
            "url" => [
                "meta" => true,
                "attr" => "id",
                "prefix" => DoctorController::DOCTOR_RESOURCES
            ],
            "structured" => false
        ],
        "departement" => DepartementController::ALL_DEPARTEMENT_RESPONSE_FORMAT,
        "specialization" => "specialization",
        "short_description" => "short_description",
        "rate" => "rate",
        'assigned_at' => 'assigned_at',
        "structured" => true
    ];

    public const PATIENT_READ_RESPONSE_FORMAT = [   // Patient's View on Doctor's Data
        "user" => [
            "id" => "id",
            "first_name" => "first_name",
            "last_name" => "last_name",
            "phone_number" => "phone_number",
            "gender" => "gender",
            "email" => "email",
            "profile_picture_path" => [
                "meta" => true,
                "attr" => "profile_picture",
                "prefix" => Controller::STORAGE_URL
            ],
            "url" => [
                "meta" => true,
                "attr" => "id",
                "prefix" => DoctorController::DOCTOR_RESOURCES
            ],
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
     *      summary = "add a new doctor to the system",
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
     *                  "assigned_at",
     *                  "rate",
     *              },
     *              @OA\Property(property="first_name",type="string"),
     *              @OA\Property(property="last_name",type="string"),
     *              @OA\Property(property="email",type="string"),
     *              @OA\Property(property="password",type="string"),
     *              @OA\Property(property="phone_number",type="string"),
     *              @OA\Property(property="address",type="string"),
     *              @OA\Property(property="gender",type="integer", ref="#/components/schemas/Gender"),
     *              @OA\Property(property="birth_date",type="date"),
     *              @OA\Property(property="departement_id",type="integer"),
     *              @OA\Property(property="specialization",type="integer" , ref="#/components/schemas/MedicalSpecialization"),
     *              @OA\Property(property="short_description",type="string"),
     *              @OA\Property(property="assigned_at",type="date"),
     *              @OA\Property(property="rate",type="integer" , ref="#/components/schemas/Rate")
     *          ),
     *      ),
     *  ),
     * )
     */
    protected function create(DoctorForm $request) {
        $this->authorize('create' , Doctor::class);
        
        $validated = $request->validated();
        if ( !key_exists("departement_id" , $validated) ) {
            throw new HttpResponseException(response()->json([
                "errors" => [
                    "departement_id" => [
                        "departement_id should be supported for this end-point"
                    ]
                ]
            ]));
        }

        DB::beginTransaction();
        $status_code = 0;
        $repsonse_data = [];
        $image = $request->file('profile_picture');

        try {

            if ($image) {
                $image_path = $image->store("uploads/images" , "public");
                $validated["profile_picture"] = $image_path;
            }

            $user = User::create(array_merge($validated , ["role_id" => Role::DOCTOR]));
            $user->markEmailAsVerified();

            $doctor = Doctor::create(array_merge([
                'user_id' => $user->id
            ] , $validated));

            DB::commit();
     
            $status_code = 200;
            $repsonse_data = [
                "status" => "created" , 
                "data" => Controller::formatData($doctor , DoctorController::ADMIN_READ_RESPONSE_FORMAT)
            ];
     
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
     *      tags={"Admin" , "Anonymous"},
     *      operationId = "readDoctor",
     *      summary = "read a doctor info",
     *      description= "Read a Specific Doctor Info Endpoint.",
     *      @OA\Parameter(name="full-detailed" , description="full-detailed doctor" , in="query" , required=false,
     *          @OA\Schema(
     *              type="boolean"
     *          )),
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
    protected function read(Request $request, $id) {
        $doctor = DoctorController::getDoctorOr404($id);
        $full_detailed_query = $request->query("full-detailed" , false);

        if($full_detailed_query) 
            $this->authorize("view" , $doctor);
        
            $response_format =   
            $full_detailed_query ?  
            DoctorController::ADMIN_READ_RESPONSE_FORMAT : 
            DoctorController::PATIENT_READ_RESPONSE_FORMAT;
        return response()->json(
            Controller::formatData(
                $doctor ,
                $response_format
            ),200);
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
     *              @OA\Property(property="gender",type="integer" , ref="#/components/schemas/Gender"),
     *              @OA\Property(property="birth_date",type="date"),
     *              @OA\Property(property="departement_id",type="integer"),
     *              @OA\Property(property="specialization",type="integer" , ref="#/components/schemas/MedicalSpecialization"),
     *              @OA\Property(property="short_description",type="string"),
     *              @OA\Property(property="assigned_at",type="date"),
     *              @OA\Property(property="rate",type="integer" , ref="#/components/schemas/Rate")
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
        $image = $request->file('profile_picture');
        
        $status_code = 0;
        $response_data = [];


        DB::beginTransaction();
        try {

            if ($image) {
                $image_path = $image->store("uploads/images" , "public");
                $validated["profile_picture"] = $image_path;
            }

            $doctor->user->update($validated);
            $doctor->update($validated);

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
            error_log($exp);
            DB::rollBack();

            $status_code = 500;
        }
        
        return response()->json($response_data , $status_code);
    }

    /**
     *  @OA\Get(
     *      path="/api/doctors/",
     *      tags={"Admin" , "Anonymous"},
     *      operationId = "List Doctors",
     *      summary = "list all doctors",
     *      @OA\Parameter(name="full-detailed" , description="full-detailed doctor" , in="query" , required=false,
     *          @OA\Schema(
     *              type="boolean"
     *          )),
     *      description= "List All Doctors Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden")
     *  )
     */
    protected function index(Request $request){
        $doctors = Doctor::all();
        $full_detailed_query = $request->query("full-detailed" , false);
        
        if($full_detailed_query)
            $this->authorize("viewAny" , Doctor::class);

        $response_format = 
            $full_detailed_query ?
            DoctorController::ADMIN_INDEX_RESPONSE_FORMAT :
            DoctorController::PATIENT_INDEX_RESPONSE_FORMAT;

        return response()->json(Controller::paginate(
            Controller::formatCollection(
                $doctors,
                $response_format
            )
        ));
    }


    /**
     *  @OA\Get(
     *      path="/api/doctors/search/",
     *      tags={"Anonymous"},
     *      operationId = "DoctorSearch",
     *      summary = "Seach on doctors using their full names",
     *      description= "Seach on Doctors Endpoint.",
     *      @OA\Parameter(name="name" , description="doctor's full name" , in="query" , required=false,
     *          @OA\Schema(
     *              type="string"
     *          )),
     *      @OA\Parameter(name="spec" , description="doctor's specialization" , in="query" , required=false,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="401", description="Unauthorized")
     *  )
     */
    public function search(Request $request) {

        $doctor_name = htmlentities($request->query("name"));
        $doctor_spec = htmlentities($request->query("spec"));

        $suggested_doctors = new Collection();

        if ($doctor_name)
            $suggested_doctors = 
                Doctor::join("users" , "users.id" , "doctors.user_id")
                ->where(
                    DB::raw(
                        "concat(first_name , ' ' , last_name)"
                    ) , "LIKE" , "%".$doctor_name."%")->get();

        if($doctor_spec != null) 
            $suggested_doctors = 
                Doctor::where("specialization" , $doctor_spec)->get();


        return response()->json(Controller::paginate(
            Controller::formatCollection(
                $suggested_doctors,
                DoctorController::PATIENT_READ_RESPONSE_FORMAT
            )
        ), 200);
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
     *       @OA\Response(response="401", description="Unauthorized")
     *  )
     */
    public function me(Request $request) {
        $current_user = $request->user();
        if ( $current_user == null ) {
            return response()->json([
                "details" => "current user is undefined"
            ],401);
        }
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
                DoctorController::DOCTOR_READ_RESPONSE_FORMAT
            )
        );
    }



    /**
     *  @OA\Put(
     *       path="/api/doctors/me",
     *       tags={"Doctor"},
     *       operationId = "updateCurrentDoctor",
     *       description= "Update Doctor's Personal Info Endpoint.",
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
     *                  @OA\Property(property="gender",type="integer" , ref="#/components/schemas/Gender"),
     *                  @OA\Property(property="birth_date",type="date"),
     *              ),
     *          ),
     *       @OA\Response(response="200", description="OK"),
     *       @OA\Response(response="403", description="Forbidden"),
     *       @OA\Response(response="422", description="Unprocessable Content"),
     *       @OA\Response(response="401", description="Unauthorized")
     *  )
     */
    public function updateMe(UserForm $request) {
        $current_user = $request->user();
        if ( $current_user == null ) {
            return response()->json([
                "details" => "current user is undefined"
            ],401);
        }

        $current_doctor = Doctor::where(
            "user_id" , $current_user->id
        )->first();
        if ( $current_doctor == null ) {
            return response()->json([
                "details" => "the current user is not a doctor"
            ] , 403);
        }
        
        $validated = $request->validated();
        $image = $request->file('profile_picture');

        try{
            if ($image) {
                $image_path = $image->store("uploads/images" , "public");
                $validated["profile_picture"] = $image_path;
            }
            
            $current_user->update($validated);
            
        } catch (Exception $exp) {
            return response()->json(["status" => "failed" , "details" => $exp] , 500);
        }
        
        return response()->json(
            ["status" => "updated" , "data" => $validated]
        );
    }




    /**
     *  @OA\Get(
     *      path="/api/statistics/best_doctors",
     *      tags={"Anonymous"},
     *      operationId = "bestDoctors",
     *      summary = "list the best four doctors",
     *      description= "Best Doctors Report Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="401", description="Unauthorized"),
     *  )
     */
    public function bestDoctors() {
        
        $best_doctors = Doctor::orderBy('rate' , 'DESC')->take(4)->get();
        return response()->json([
            "details" => "best doctors in the hospital",
            "data" => Controller::formatCollection(
                $best_doctors,
                DoctorController::PATIENT_READ_RESPONSE_FORMAT
            )
        ]);
    }
}
