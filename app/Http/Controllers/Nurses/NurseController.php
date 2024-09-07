<?php

namespace App\Http\Controllers\Nurses;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Requests\NurseForm;
use App\Http\Requests\UserForm;
use App\Models\Nurse;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class NurseController extends Controller
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
        ],

        "doctor_id" => "doctor_id",
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
            "profile_picture_path" => "profile_picture_path"
        ],
        "doctor_id" => "doctor_id",
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
            "email" => "email",
            "phone_number" => "phone_number",
            "gender" => "gender",
            "profile_picture_path" => "profile_picture_path",
        ],
        "specialization" => "specialization",
        "rate" => "rate",
        "structured" => true
    ];

    public static function getNurseOr404($nurseId) {
        $nurse = Nurse::where('user_id' , $nurseId)->first();
        if ( $nurse == null ) {
            abort(404 , "nurse does not exist");       
        }
        return $nurse;
    } 

    
    /**
     *  @OA\Get(
     *       path="/api/nurses/{id}",
     *       tags={"Admin"},
     *       operationId = "readNurse",
     *       summary = "read a nurse info",
     *       description= "Read Nurse Endpoint.",
     *       @OA\Parameter(name="id", description="nurse's id" , in="path", required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *       @OA\Response(response="200", description="OK"),
     *       @OA\Response(response="403", description="Forbidden"),
     *       @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    protected function read($id) {
        $nurse = NurseController::getNurseOr404($id);
        $this->authorize('view' , $nurse);
        return response()->json(
            Controller::formatData(
                $nurse , 
                NurseController::ADMIN_READ_RESPONSE_FORMAT
            ), 200
        );
    }


    /**
     *  @OA\Put(
     *      path="/api/nurses/{id}",
     *      tags={"Admin"},
     *      operationId = "updateNurse",
     *      summary = "update a nurse",
     *      description= "Update Nurse Endpoint.",
     *      @OA\Parameter(name="id", description="nurse's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),     
     *      @OA\Response(response="422", description="Unprocessable Content"),
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
     *              @OA\Property(property="rate",type="integer", enum=App\Enums\Rate::class),
     *              @OA\Property(property="doctor_id",type="integer"),
     *          ),
     *      ),
     *  )
     */
    protected function update(NurseForm $request , $id) {
        $nurse = NurseController::getNurseOr404($id);
        $this->authorize('update' , $nurse);

        $validated = $request->validated();
        $user_data = Arr::except($validated , ['rate' , 'short_description' , 'specialization' , 'doctor_id']);

        $response_data = [];
        $status_code = 0;

        DB::beginTransaction();
        try {
            $nurse->user->update($user_data);
            $nurse->update([
                "rate" => $validated["rate"] ?? $nurse->rate,
                "short_description" => $validated["short_description"] ?? $nurse->short_description,
                "doctor_id" => $validated["doctor_id"] ?? $nurse->doctor_id,
                "specialization" => $validated["specialization"] ?? $nurse->specialization,
                "'doctor_id'" => $validated['doctor_id'] ?? $nurse->doctor_id
            ]);
            DB::commit();

            $response_data = Controller::formatData(
                $nurse , 
                NurseController::ADMIN_READ_RESPONSE_FORMAT
            );
            $status_code = 200;

        } catch (Exception $exp) {
            DB::rollBack();
            $status_code = 500;
        }

        return response()->json($response_data , $status_code);
    }

    /**
     *  @OA\Delete(
     *      path="/api/nurses/{id}",
     *      tags={"Admin"},
     *      operationId = "deleteNurse",
     *      summary = "delete a nurse",
     *      description= "Delete Nurse Endpoint.",
     *      @OA\Parameter(name="id", description="nurse's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="204", description="No Content"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"), 
     *  )
     */
    protected function delete($id) {
        $nurse = NurseController::getNurseOr404($id);
        $this->authorize('delete' , $nurse);

        $user = $nurse->user;

        $status_code = 0;
        $response_data = [];
        
        DB::beginTransaction();
        try {
            $nurse->delete();
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
     *      path="/api/nurses/",
     *      tags={"Admin"},
     *      operationId = "listNurses",
     *      summary = "list all nurses",
     *      description= "List Nurses Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    protected function index(){
        $this->authorize("viewAny" , Nurse::class);
        $nurses = Nurse::all(); 
        return response()->json(
            $this->paginate(
                Controller::formatCollection(
                    $nurses,
                    NurseController::ADMIN_INDEX_RESPONSE_FORMAT
                )
            )
        );
    }

    /**
     *  @OA\Get(
     *      path="/api/nurses/{id}/doctor/",
     *      tags={"Admin"},
     *      operationId = "nurseDoctor",
     *      summary = "get responsible doctor",
     *      @OA\Parameter(name="id", description="nurse's id" , in="path", required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      description= "Get Responsible Doctor Endpoint.",
     *      @OA\Response(response="200" , description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    protected function doctor($id) {
        $nurse = NurseController::getNurseOr404($id);
        $this->authorize("view" , $nurse);
        return response(
            Controller::formatData(
                $nurse->doctor,
                DoctorController::PATIENT_READ_RESPONSE_FORMAT
            )
        );
    }


    /**
     *  @OA\Get(
     *      path="/api/nurses/me/",
     *      tags={"Nurse"},
     *      operationId = "currentNurses",
     *      summary = "read the current nurse",
     *      description= "Current Nurse Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    protected function me(Request $request) {
        $current_user = $request->user();
        $current_nurse = 
            Nurse::where(
                "user_id",
                $current_user->id
            )->first();
        if ( $current_nurse == null ) {
            return response()->json([
                "details" => "current user is not a nurse"
            ],403);
        }

        return response()->json(
            Controller::formatData(
                $current_nurse,
                NurseController::ADMIN_READ_RESPONSE_FORMAT
            )
        );
    }

    /**
     *  @OA\Put(
     *       path="/api/nurses/me",
     *       tags={"Nurse"},
     *       operationId = "updateCurrentNurse",
     *       summary = "update personal info for current nurse",
     *          @OA\RequestBody(
     *              @OA\JsonContent(
     *                  type="object",
     *                  @OA\Property(property="first_name",type="string"),
     *                  @OA\Property(property="last_name",type="string"),
     *                  @OA\Property(property="email",type="string"),
     *                  @OA\Property(property="password",type="string"),
     *                  @OA\Property(property="phone_number",type="string"),
     *                  @OA\Property(property="address",type="string"),
     *                  @OA\Property(property="gender",type="integer", enum=App\Enums\Gender::class),
     *                  @OA\Property(property="birth_date",type="date"),
     *              ),
     *          ),
     *       description= "Update Nurse's Personal Info Endpoint.",
     *       @OA\Response(response="200", description="OK"),
     *       @OA\Response(response="403", description="Forbidden"),
     *       @OA\Response(response="422", description="Unprocessable Content")
     *  )
     */
    public function updateMe(UserForm $request) {
        $current_user = $request->user();
        $current_nurse = Nurse::where(
            "user_id" , $current_user->id
        )->first();
        if ( $current_nurse == null ) {
            return response()->json([
                "details" => "the current user is not a nurse"
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
     *      path="/api/nurses/me/doctor/",
     *      tags={"Nurse"},
     *      operationId = "currentNurseDoctor",
     *      summary = "read responsible doctor for the current nurse",
     *      description= "Current Nurse's Responsible Doctor Endpoint.",
     *      @OA\Response(response="200" , description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    protected function myDoctor(Request $request) {
        $current_user = $request->user();
        $current_nurse = 
            Nurse::where(
                "user_id",
                $current_user->id
            )->first();
        if ( $current_nurse == null ) {
            return response()->json([
                "details" => "current user is not a nurse"
            ],403);
        }

        return response()->json(
            Controller::formatData(
                $current_nurse->doctor,
                DoctorController::PATIENT_READ_RESPONSE_FORMAT
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

