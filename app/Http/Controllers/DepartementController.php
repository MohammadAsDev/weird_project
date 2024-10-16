<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Controllers\Nurses\NurseController;
use App\Http\Requests\ClinicForm;
use App\Http\Requests\DepartementForm;
use App\Http\Requests\DoctorForm;
use App\Http\Requests\NurseForm;
use App\Models\Clinic;
use App\Models\Departement;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartementController extends Controller
{
    public const DEPARTEMENTS_RESOURCES = Controller::APP_URL . "api/departements/";

    public const ALL_DEPARTEMENT_RESPONSE_FORMAT = [
        "url" => [
            "attr" => "id",
            "prefix" => DepartementController::DEPARTEMENTS_RESOURCES,
            "meta" => true
        ],
        "departement_name" => "name",
        "specialization" =>  "specialization",
        "description" => "description",
        "structured" => true
    ];


    private function getDepartementOr404($departementId) {
        $departement = Departement::where('id' , $departementId)->first();
        if ( $departement == null ) {
            abort(404 , "departement does not exist");       
        }
        return $departement;
    }

    /**
     *  @OA\Post(
     *      path="/api/departements",
     *      tags={"Admin"},
     *      operationId = "createDepartements",
     *      summary = "create a departement",
     *      description= "Create Departement Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={
     *                  "name",
     *                  "specialization",
     *                  "description",
     *              },
     *              @OA\Property(property="name",type="string"),
     *              @OA\Property(property="specialization",type="integer"),
     *              @OA\Property(property="description",type="string"),
     *          ),
     *      ),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *  )
     */
    protected function create(DepartementForm $request) {
        $this->authorize('create' , Departement::class);
        
        $validated = $request->validated();

        Departement::create($validated);

        return response()->json([
            "status" => "created",
            "result" => $validated
        ], 200);
    }

    
    /**
     *  @OA\Get(
     *      path="/api/departements/{id}",
     *      tags={"Admin"},
     *      operationId = "readDepartement",
     *      summary = "read a departement",
     *      description= "Read Departement Endpoint.",
     *      @OA\Parameter(name="id", description="departement's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    protected function read($id) {
        $departement = $this->getDepartementOr404($id);
        $this->authorize('view' , $departement);
        return response()->json($departement, 200);
    }


    /**
     *  @OA\Put(
     *      path="/api/departements/{id}",
     *      tags={"Admin"},
     *      operationId = "updateDepartement",
     *      summary = "update a departement",
     *      description= "Update Departement Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="departement_name",type="string"),
     *              @OA\Property(property="specialization",type="integer"),
     *              @OA\Property(property="description",type="string"),
     *          ),
     *      ),
     *      @OA\Parameter(name="id", description="departement's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),     
     *      @OA\Response(response="422", description="Unprocessable Content")
     *  )
     */
    protected function update(DepartementForm $request , $id) {
        $departement = $this->getDepartementOr404($id);
        $this->authorize('update' , $departement);

        $validated = $request->validated();
        $departement->update($validated);

        return response()->json($departement, 200);
    }

    /**
     * @OA\Delete(
     *      path="/api/departements/{id}",
     *      tags={"Admin"},
     *      operationId = "deleteDepartement",
     *      summary = "delete a departement",
     *      description= "Delete Departement Endpoint.",
     *      @OA\Parameter(name="id", description="departement's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="204", description="No Content"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"), 
     * )
     */
    protected function delete($id) {
        $departement = $this->getDepartementOr404($id);
        $this->authorize('delete' , $departement);

        $departement->delete();
        return response()->json([], 204);
    }


    /**
     *  @OA\GET(
     *      path="/api/departements/",
     *      tags={"Admin"},
     *      operationId = "listDepartements",
     *      @OA\Parameter(name="paginated" , description="enable paginated" , in="query" , required=false,
     *          @OA\Schema(
     *              type="boolean"
     *          )),
     *      summary = "list departements",
     *      description= "List Departements Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    protected function index(Request $request){
        $this->authorize("viewAny" , Departement::class);
        $departements = Departement::all();
        $paginated = htmlentities($request->query("paginated"));
        $paginated = $paginated == null ? true : boolval($paginated);
        if ($paginated)
            return response()->json(
                Controller::paginate($departements)
            );
        return response()->json($departements);
    }

    /**
     *  @OA\Post(
     *      path="/api/departements/{id}/doctors/",
     *      tags={"Admin"},
     *      operationId = "createDepartementDoctor",
     *      summary = "create a departement's doctor",
     *      description= "Create Doctor's Departement Endpoint.",
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
     *                  "specialization",
     *                  "short_description",
     *                  "rate",
     *                  "assigned_at",
     *              },
     *              @OA\Property(property="first_name",type="string"),
     *              @OA\Property(property="last_name",type="string"),
     *              @OA\Property(property="email",type="string"),
     *              @OA\Property(property="password",type="string"),
     *              @OA\Property(property="phone_number",type="string"),
     *              @OA\Property(property="address",type="string"),
     *              @OA\Property(property="gender",type="integer" , ref="#/components/schemas/Gender"),
     *              @OA\Property(property="birth_date",type="date"),
     *              @OA\Property(property="specialization",type="integer",ref="#/components/schemas/MedicalSpecialization"),
     *              @OA\Property(property="short_description",type="string"),
     *              @OA\Property(property="rate",type="integer",ref="#/components/schemas/Rate"),
     *              @OA\Property(property="assigned_at",type="date"),
     *          ),
     *     ),
     *     @OA\Parameter(name="id", description="departement's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="Object Not Found"),
     *     @OA\Response(response="422", description="Unprocessable Content"),
     *  )
     */
    protected function createDoctor(DoctorForm $request , $id) { 
        $this->getDepartementOr404($id);
        $this->authorize("create" , Doctor::class);
        $validated = $request->validated();
        
        $status_code = 0;
        $response_data = [];

        DB::beginTransaction();
        try {
            $doctor_user = User::create(array_merge(
                $validated , 
                ["role_id" => Role::DOCTOR]
            ));
            Doctor::create(array_merge(
                $validated , 
                ["departement_id" => $id , "user_id" => $doctor_user->id]
            ));
            $doctor_user->markEmailAsVerified();

            $request->image->storeAs('images' , $validated["profile_image"]);

            DB::commit();
            
            $status_code = 200;
            $response_data = ["status" => "created" , "data" => $validated];
        } catch (Exception $expception ) {
            DB::rollBack();
            $status_code = 500;
            $response_data = ["status" => "uncreated"];
        }

        return response()->json($response_data , $status_code);
    }

    /**
     *  @OA\Get(
     *     path="/api/departements/{id}/doctors/",
     *     tags={"Admin"},
     *     operationId = "listDepartementDoctors",
     *     summary = "list departement's doctors",
     *     description= "List Doctors Working in Specific Departement Endpoint.",
     *     @OA\Parameter(name="id", description="departement's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    protected function listDoctors($id) {
        $this->getDepartementOr404($id);
        $this->authorize("viewAny" , Doctor::class);

        $doctors = Doctor::where("departement_id" , $id)->get();
        return response()->json(Controller::paginate(
            Controller::formatCollection(
                $doctors,
                DoctorController::ADMIN_INDEX_RESPONSE_FORMAT
            )
        ));
    }


    /**
     *  @OA\Post(
     *     path="/api/departements/{id}/nurses/",
     *     tags={"Admin"},
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
     *                  "specialization",
     *                  "short_description",
     *                  "rate",
     *                  "assinged_at"
     *              },
     *              @OA\Property(property="first_name",type="string"),
     *              @OA\Property(property="last_name",type="string"),
     *              @OA\Property(property="email",type="string"),
     *              @OA\Property(property="password",type="string"),
     *              @OA\Property(property="phone_number",type="string"),
     *              @OA\Property(property="address",type="string"),
     *              @OA\Property(property="gender",type="integer"),
     *              @OA\Property(property="birth_date",type="date"),
     *              @OA\Property(property="specialization",type="integer"),
     *              @OA\Property(property="short_description",type="string"),
     *              @OA\Property(property="assigned_at",type="date"),
     *              @OA\Property(property="rate",type="integer"),
     *          ),
     *     ),
     *     @OA\Parameter(name="id", description="departement's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="Object Not Found"),
     *     @OA\Response(response="422", description="Unprocessable Content"),
     *  )
     */
    protected function createNurse(NurseForm $request , $id) { 
        $this->getDepartementOr404($id);
        $this->authorize("create" , Nurse::class);
        $validated = $request->validated();

        $status_code = 0;
        $response_data = [];

        DB::beginTransaction();
        try {
            $user = User::create(array_merge($validated , ["role_id" => Role::NURSE]));
            Nurse::create(array_merge(
            $validated, [
                "user_id" => $user->id,
                "departement_id" => $id
            ]));
            $user->markEmailAsVerified();

            $request->image->storeAs('images' , $validated["profile_image_path"]);

            DB::commit();

            $status_code = 200;
            $response_data = ["status" => "created" , "data" => $validated];

        } catch ( Exception $expception) {
            DB::rollBack();
            $status_code = 500;
            $response_data = ["status" => "uncreated"];
        }
        return response()->json($response_data , $status_code);
    }


    /**
     * @OA\Get(
     *     path="/api/departements/{id}/nurses/",
     *     tags={"Admin"},
     *     operationId = "listDepartementNurses",
     *     summary = "list departement's nurses",
     *     description= "List Nurses Working in Specific Departement Endpoint.",
     *     @OA\Parameter(name="id", description="departement's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="Object Not Found"),
     * )
     */
    protected function listNurses($id) {
        $this->getDepartementOr404($id);
        $this->authorize("viewAny" , Nurse::class);

        $nurses = Nurse::where("departement_id" , $id)->get();
        return response()->json(Controller::paginate(
            Controller::formatCollection(
                $nurses,
                NurseController::ADMIN_INDEX_RESPONSE_FORMAT
            )
        ));
    }


    /**
     *  @OA\Post(
     *      path="/api/departements/{id}/clinics/",
     *      tags={"Admin"},
     *      operationId = "createDepartementClinics",
     *      summary = "create departement's clinic",
     *      description= "Create Departement's Clinic Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={
     *                  "clinic_code",
     *              },
     *              @OA\Property(property="clinic_code",type="string"),
     *          ),
     *      ),
     *     @OA\Parameter(name="id", description="departement's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *  )
     */
    protected function createClinic(ClinicForm $request , $id) {
        $this->authorize("createClinic" , Departement::class);

        $validated = $request->validated();
        Clinic::create(array_merge(
            $validated, [
                "departement_id" => $id,
            ]
        ));

        return response()->json([
            "status" => "created",
            "data" => $validated
        ]);
    }
    

    /**
     *  @OA\Get(
     *      path="/api/departements/{id}/clinics/",
     *      tags={"Admin"},
     *      operationId = "listDepartementClinics",
     *      summary = "list departement's clinics",
     *      description= "List Clinics in Specific Departement Endpoint.",
     *      @OA\Parameter(name="id", description="departement's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    protected function listClinics($id) {
        $departement = $this->getDepartementOr404($id);
        $this->authorize("viewAny" , Nurse::class);

        return response()->json(Controller::paginate(
            Controller::formatCollection(
                $departement->clinics,
                ClinicController::PATIENT_CLINIC_INDEX_RESPONSE_FOMAT
            )
        ));
    }
    
}
