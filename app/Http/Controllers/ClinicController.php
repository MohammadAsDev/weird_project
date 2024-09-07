<?php

namespace App\Http\Controllers;

use App\Enums\ClinicType;
use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Requests\ClinicForm;
use App\Http\Requests\ExternalClinicForm;
use App\Http\Requests\InternalClinicForm;
use App\Models\Clinic;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ClinicController extends Controller
{

    // Exnternal Clinics Response Format

    const PATIENT_EXTERNAL_CLINIC_ONLY_RESPONSE_FORMAT = [
        "id" => "id",
        "clinic_longitude" => "clinic_longitude",
        "clinic_latitude" => "clinic_latitude",
        "doctor_id" => "doctor_id",
        "structured" => true
    ];

    const PATIENT_EXTERNAL_INDEX_RESPONSE_FORMAT = [
        "id" => "id",
        "clinic_longitude" => "clinic_longitude",
        "clinic_latitude" => "clinic_latitude",
        "doctor_id" => "doctor_id",
        "structured" => true
    ];

    const PATIENT_EXTERNAL_CLINIC_RESPONSE_FORMAT = [
        "id" => "id",
        "clinic_longitude" => "clinic_longitude",
        "clinic_latitude" => "clinic_latitude",
        "doctor" =>  DoctorController::PATIENT_READ_RESPONSE_FORMAT,
        "structured" => true
    ];

    // Internal Clinics Response Format


    const PATIENT_INTERNAL_CLINIC_ONLY_RESPONSE_FORMAT = [
        "id" => "id",
        "clinic_code" => "clinic_code",
        "departement_id" => "departement_id",
        "structured" => true
    ];

    const PATIENT_INTERNAL_INDEX_RESPONSE_FORMAT = [
        "id" => "id",
        "clinic_code" => "clinic_code",
        "departement_id" => "departement_id",
        "doctor_id" => "doctor_id",
        "structured" => true
    ];

    const PATIENT_INTERNAL_CLINIC_RESPONSE_FORMAT = [
        "id" => "id",
        "clinic_code" => "clinic_code",
        "departement" => DepartementController::ALL_DEPARTEMENT_RESPONSE_FORMAT,
        "doctor" => DoctorController::PATIENT_READ_RESPONSE_FORMAT,
        "structured" => true 
    ];

    // General Clinics Response Format

    const PATIENT_CLINIC_ONLY_RESPONSE_FOMAT = [
        "id" => "id",
        "clinc_type" => "clinic_type",
        "departement" => DepartementController::ALL_DEPARTEMENT_RESPONSE_FORMAT,
        "clinic_code" => "clinic_code",
        "clinic_longitude" => "clinic_longitude",
        "clinic_latitude" => "clinic_latitude",
        "structured" => true
    ];

    const PATIENT_CLINIC_INDEX_RESPONSE_FOMAT = [
        "id" => "id",
        "clinc_type" => "clinic_type",
        "departement_id" => "departement_id",
        "clinic_code" => "clinic_code",
        "clinic_longitude" => "clinic_longitude",
        "clinic_latitude" => "clinic_latitude",
        "doctor_id" => "doctor_id",
        "structured" => true
    ];


    const PATIENT_CLINIC_RESPONSE_FOMAT = [
        "id" => "id",
        "clinc_type" => "clinic_type",
        "departement" =>  DepartementController::ALL_DEPARTEMENT_RESPONSE_FORMAT,
        "clinic_code" => "clinic_code",
        "clinic_longitude" => "clinic_longitude",
        "clinic_latitude" => "clinic_latitude",
        "doctor" => DoctorController::PATIENT_READ_RESPONSE_FORMAT,
        "structured" => true
    ];


    public static function getClinicOr404($clinicId) {
        $clinic = Clinic::where("id" , $clinicId)->first();
        if ( $clinic == null ) {
            abort(404 , "clinic does not exist");
        }
        return $clinic;
    }

    public static function getInternalClinicOr404($clinicId) {
        $clinic = Clinic::where("clinic_type" , ClinicType::INTERNAL->value)->where("id" , $clinicId)->first();
        if ( $clinic == null ) {
            abort(404 , "clinic does not exist");
        }
        return $clinic;
    }

    public static function getExternalClinicOr404($clinicId) {
        $clinic = Clinic::where("clinic_type" , ClinicType::EXTERNAL)->where("id" , $clinicId)->first();
        if ( $clinic == null ) {
            abort(404 , "clinic does not exist");
        }
        return $clinic;
    }

    /**
     *  @OA\Get(
     *      path="/api/clinics",
     *      tags={"Admin"},
     *      operationId = "listClinics",
     *      summary = "list all clinics",
     *      description= "List Clinics Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function index() {
        $this->authorize('viewAny' , Clinic::class);
        $clinics = Clinic::all();
        return response()->json($this->paginate(
            Controller::formatCollection(
                $clinics,
                ClinicController::PATIENT_CLINIC_INDEX_RESPONSE_FOMAT
            )
        ));
    }


    /**
     *  @OA\Get(
     *      path="/api/clinics/internal",
     *      tags={"Admin" , "Patient"},
     *      operationId = "listInternalClinics",
     *      summary = "list all internal clinics",
     *      description= "List Internal Clinics Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function listInternalClinics() {
        $this->authorize('viewAny', Clinic::class);
        $clinics = Clinic::where('clinic_type' , ClinicType::INTERNAL)->get();
        
        return response()->json(
            $this->paginate(
                Controller::formatCollection(
                    $clinics,
                    ClinicController::PATIENT_INTERNAL_INDEX_RESPONSE_FORMAT
                )
            )
        );
    }


    /**
     *  @OA\Get(
     *      path="/api/clinics/internal/{id}",
     *      tags={"Admin" , "Patient"},
     *      operationId = "readInternalClinics",
     *      summary = "read internal clinic",
     *      description= "Read Internal Clinic Endpoint.",
     *      @OA\Parameter(name="id", description="clinic's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    public function readInternalClinic($id) {
        $clinic = ClinicController::getInternalClinicOr404($id);
        $this->authorize('view' , $clinic);
        return response()->json(Controller::formatData(
                $clinic, 
                ClinicController::PATIENT_INTERNAL_CLINIC_RESPONSE_FORMAT
            )
        );
    }

    /**
     *  @OA\Get(
     *      path="/api/clinics/external",
     *      tags={"Admin" , "Patient"},
     *      operationId = "listExternalClinics",
     *      summary = "list external clinics",
     *      description= "List External Clinics Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function listExternalClinics() {
        $this->authorize('viewAny', Clinic::class);
        $clinics = Clinic::where('clinic_type' , ClinicType::EXTERNAL)->get();
        
        return response()->json($this->paginate(
            Controller::formatCollection(
                $clinics,
                ClinicController::PATIENT_EXTERNAL_INDEX_RESPONSE_FORMAT
            )
        ));
    }

    /**
     *   @OA\Get(
     *      path="/api/clinics/external/{id}",
     *      tags={"Admin" , "Patient"},
     *      operationId = "readExternalClinics",
     *      summary = "read external clinic",
     *      description= "Read External Clinic Endpoint.",
     *      @OA\Parameter(name="id", description="clinic's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    public function readExternalClinic($id) {
        $clinic = ClinicController::getExternalClinicOr404($id);
        $this->authorize('view' , $clinic);
        return response()->json(Controller::formatData(
                $clinic, 
                ClinicController::PATIENT_EXTERNAL_CLINIC_RESPONSE_FORMAT
            )
        );
    }

    /**
     *  @OA\Post(
     *      path="/api/clinics/external",
     *      tags={"Admin"},
     *      operationId = "createExternalClinics",
     *      summary = "create external clinic",
     *      description= "Create External Clinic Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={
     *                  "doctor_id",
     *                  "clinic_longitude",
     *                  "clinic_latitude",
     *              },
     *              @OA\Property(property="doctor_id",type="integer"),
     *              @OA\Property(property="clinic_longitude",type="number"),
     *              @OA\Property(property="clinic_latitude",type="number"),
     *          ),
     *      ),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *  )
     */
    public function createExternalClinic(ExternalClinicForm $request) {
        $this->authorize('create' , Clinic::class);
        $validated = $request->validated();
        Clinic::create(array_merge($validated , ["clinic_type" => ClinicType::EXTERNAL]));
        return response()->json(["status" => "created" , "data" => $validated] , 200);
    }


    /**
     *  @OA\Post(
     *      path="/api/clinics/internal",
     *      tags={"Admin"},
     *      operationId = "createInteranlClinics",
     *      summary = "create internal clinic",
     *      description= "Create Internal Clinic Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={
     *                  "doctor_id",
     *                  "clinic_code",
     *                  "departement_id",
     *              },
     *              @OA\Property(property="doctor_id",type="integer"),
     *              @OA\Property(property="clinic_code",type="string"),
     *              @OA\Property(property="departement_id",type="integer"),
     *          ),
     *      ),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function createInternalClinic(InternalClinicForm $request) {
        $this->authorize('create' , Clinic::class);
        $validated = $request->validated();
        Clinic::create(array_merge($validated , ["clinic_type" => ClinicType::INTERNAL]));
        return response()->json([
            "status" => "created",
            "data" => $validated    
        ] , 200);
    }


    /**
     *  @OA\Put(
     *      path="/api/clinics/{id}",
     *      tags={"Admin"},
     *      operationId = "updateClinics",
     *      summary = "update clinic",
     *      description= "Update Clinic Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={
     *                  "doctor_id",
     *                  "clinic_code",
     *                  "departement_id",
     *                  "clinic_longitude",
     *                  "clinic_latitude",
     *              },
     *              @OA\Property(property="doctor_id",type="integer"),
     *              @OA\Property(property="clinic_code",type="string"),
     *              @OA\Property(property="departement_id",type="integer"),
     *              @OA\Property(property="clinic_longitude",type="number"),
     *              @OA\Property(property="clinic_latitude",type="number"),
     *          ),
     *      ),
     *      @OA\Parameter(name="id", description="clinic's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    public function update(ClinicForm $request, $id) {
        $clinic = ClinicController::getClinicOr404($id);
        $this->authorize('update' , $clinic);
        $validated = $request->validated();
        $clinic->update($validated);
        return response()->json(Controller::formatData(
                $clinic , 
                ClinicController::PATIENT_CLINIC_RESPONSE_FOMAT
            )
        );
    }


    /**
     *  @OA\Delete(
     *      path="/api/clinics/{id}",
     *      tags={"Admin"},
     *      operationId = "deleteClinics",
     *      summary = "delete clinic",
     *      description= "Delete Clinic Endpoint.",
     *      @OA\Parameter(name="id", description="clinic's id" , in="path" , required=true, 
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="204", description="No Content"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    public function delete($id) {
        $clinic = ClinicController::getClinicOr404($id);
        $this->authorize('delete' , $clinic);
        $clinic->delete();
        return response()->json([] , 204);
    }


    
    public function paginate($items, $perPage = 5, $page = null, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
