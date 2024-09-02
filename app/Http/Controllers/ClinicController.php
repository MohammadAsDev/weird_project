<?php

namespace App\Http\Controllers;

use App\Enums\ClinicType;
use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Requests\ClinicForm;
use App\Http\Requests\ExternalClinicForm;
use App\Http\Requests\InternalClinicForm;
use App\Models\Clinic;
use App\Models\Departement;
use App\Models\Doctor;
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


    /**
     * @OA\Get(
     *     path="/api/clinics",
     *     tags: ["Admin"],
     *     @OA\Response(response="200", description="List all clinics"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function index() {
        $this->authorize('viewAny' , Clinic::class);
        $clinic_query = Clinic::all();
        $clinic_data = [];
        foreach ($clinic_query as $clinic) { 
            array_push($clinic_data , Controller::formatData(
                    $clinic , 
                    ClinicController::PATIENT_CLINIC_INDEX_RESPONSE_FOMAT
                )
            );
        }
        return response()->json($this->paginate($clinic_data) , 200);
    }


    /**
     * @OA\Get(
     *     path="/api/clinics/internal",
     *     tags: ["Admin" , "Patient"],
     *     @OA\Response(response="200", description="List all internal clinics"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function listInternalClinics() {
        $this->authorize('viewAny', Clinic::class);
        $clinic_data = [];
        $clinic_query = Clinic::where('clinic_type' , ClinicType::INTERNAL)->get();
        foreach($clinic_query as $clinic) {
            array_push($clinic_data , 
                Controller::formatData(
                    $clinic , 
                    ClinicController::PATIENT_INTERNAL_INDEX_RESPONSE_FORMAT
                )
            );
        }
        return response()->json($this->paginate($clinic_data) , 200);
    }


    /**
     * @OA\Get(
     *     path="/api/clinics/internal/{id}",
     *     tags: ["Admin" , "Patient"],
     *     @OA\Parameter(name="id", description="clinic's id" , in="path"),
     *     @OA\Response(response="200", description="List all internal clinics"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function readInternalClinic($id) {
        $clinic = Clinic::where("clinic_type" , ClinicType::INTERNAL)->where("id" , $id)->first();
        if ( $clinic == null ) {
            return response()->json([
                "details" => "clinic does not exist"
            ] , 404);
        }
        $this->authorize('view' , $clinic);
        return response()->json(Controller::formatData(
                $clinic, 
                ClinicController::PATIENT_INTERNAL_CLINIC_RESPONSE_FORMAT
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/clinics/external",
     *     tags: ["Admin" , "Patient"],
     *     @OA\Response(response="200", description="List all external clinics"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function listExternalClinics() {
        $this->authorize('viewAny', Clinic::class);
        $clinic_data = [];
        $clinic_query = Clinic::where('clinic_type' , ClinicType::EXTERNAL)->get();
        foreach($clinic_query as $clinic) {
            array_push($clinic_data , Controller::formatData(
                    $clinic , 
                    ClinicController::PATIENT_EXTERNAL_INDEX_RESPONSE_FORMAT
                )
            );
        }
        return response()->json($this->paginate($clinic_data) , 200);
    }

    /**
     * @OA\Get(
     *     path="/api/clinics/external/{id}",
     *     tags: ["Admin" , "Patient"],
     *     @OA\Parameter(name="id", description="clinic's id" , in="path"),
     *     @OA\Response(response="200", description="List all external clinics"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function readExternalClinic($id) {
        $clinic = Clinic::where("clinic_type" , ClinicType::EXTERNAL)->where("id" , $id)->first();
        if ( $clinic == null ) {
            return response()->json([
                "details" => "clinic does not exist"
            ] , 404);
        }
        $this->authorize('view' , $clinic);
        return response()->json(Controller::formatData(
                $clinic, 
                ClinicController::PATIENT_EXTERNAL_CLINIC_RESPONSE_FORMAT
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/clinics/external",
     *     tags: ["Admin"],
     *     @OA\Response(response="200", description="Create an external clinic"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="422", description="Invalid data"),
     * )
     */
    public function createExternalClinic(ExternalClinicForm $request) {
        $this->authorize('create' , Clinic::class);
        $validated = $request->validated();
        $doctor = Doctor::where("user_id" , $validated["doctor_id"])->first();
        if ( $doctor == null ) {
            return response()->json([
                "details" => "doctor does not exist"
            ] , 404);
        }
        Clinic::create(array_merge($validated , ["clinic_type" => ClinicType::EXTERNAL]));
        return response()->json(["status" => "created" , "data" => $validated] , 200);
    }


    /**
     * @OA\Post(
     *     path="/api/clinics/internal",
     *     tags: ["Admin"],
     *     @OA\Response(response="200", description="Create an internal clinic"),
     *     @OA\Response(response="422", description="Invalid data"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function createInternalClinic(InternalClinicForm $request) {
        $this->authorize('create' , Clinic::class);
        $validated = $request->validated();
        $doctor = Doctor::where("user_id" , $validated["doctor_id"])->first();
        if ( $doctor == null ) {
            return response()->json([
                "details" => "doctor does not exist"
            ] , 404);
        }
        $departement = Departement::where("id" , $validated["departement_id"])->first();
        if ( $departement == null ) {
            return response()->json([
                "details" => "departement does not exist"
            ], 404);
        }
        Clinic::create(array_merge($validated , ["clinic_type" => ClinicType::INTERNAL]));
        return response()->json([
            "status" => "created",
            "data" => $validated    
        ] , 200);
    }


    /**
     * @OA\Put(
     *     path="/api/clinics/{id}",
     *     tags: ["Admin"],
     *     @OA\Parameter(name="id", description="clinic's id" , in="path"),
     *     @OA\Response(response="200", description="Update a specific clinic"),
     *     @OA\Response(response="422", description="Invalid data"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function update(ClinicForm $request, $id) {
        $clinic = Clinic::where("id" , $id)->first();
        if ( $clinic == null ) {
            return response()->json([
                "details" => "clinic does not exist"
            ] , 404);
        }
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
     * @OA\Delete(
     *     path="/api/clinics/{id}",
     *     tags: ["Admin"],
     *     @OA\Parameter(name="id", description="clinic's id" , in="path"),
     *     @OA\Response(response="200", description="Update a specific clinic"),
     *     @OA\Response(response="422", description="Invalid data"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function delete($id) {
        $clinic = Clinic::where("id" , $id)->first();
        if ( $clinic == null ) {
            return response()->json([
                "details" => "clinic does not exist"
            ] , 404);
        }
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
