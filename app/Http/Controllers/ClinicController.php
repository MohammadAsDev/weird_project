<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClinicForm;
use App\Models\Clinic;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClinicController extends Controller
{
    public const CLINIC_RESOURCES = Controller::APP_URL . "api/clinics/";

    const PATIENT_CLINIC_ONLY_RESPONSE_FOMAT = [        // Patient view on clinic's data
        "url" => [
            "meta" => true,
            "attr" => "id",
            "prefix" => ClinicController::CLINIC_RESOURCES
        ],
        "clinic_id" => "id",
        "clinc_type" => "clinic_type",
        "clinic_code" => "clinic_code",
        "structured" => true
    ];

    const PATIENT_CLINIC_INDEX_RESPONSE_FOMAT = [       // Patient view on clinics index
        "url" => [
            "meta" => true,
            "attr" => "id",
            "prefix" => ClinicController::CLINIC_RESOURCES
        ],
        "clinic_id" => "id",
        "clinc_type" => "clinic_type",
        "departement" => "departement",
        "clinic_code" => "clinic_code",
        "structured" => true
    ];


    const PATIENT_CLINIC_RESPONSE_FORMAT = [            // Patient view on clinic's view
        "url" => [
            "meta" => true,
            "attr" => "id",
            "prefix" => ClinicController::CLINIC_RESOURCES
        ],
        "clinic_id" => "id",
        "clinc_type" => "clinic_type",
        "departement" =>  DepartementController::ALL_DEPARTEMENT_RESPONSE_FORMAT,
        "clinic_code" => "clinic_code",
        "structured" => true
    ];


    public static function getClinicOr404($clinicId) {
        $clinic = Clinic::where("id" , $clinicId)->first();
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
        return response()->json(Controller::paginate(
            Controller::formatCollection(
                $clinics,
                ClinicController::PATIENT_CLINIC_INDEX_RESPONSE_FOMAT
            )
        ));
    }



    /**
     *  @OA\Get(
     *      path="/api/clinics/{id}",
     *      tags={"Admin"},
     *      operationId = "readClinic",
     *      summary = "read a clinic",
     *      description= "Read Clinic Endpoint.",
     *      @OA\Parameter(name="id", description="clinic's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    public function readClinic($id) {
        $clinic = ClinicController::getClinicOr404($id);
        $this->authorize('view' , $clinic);
        return response()->json(Controller::formatData(
                $clinic, 
                ClinicController::PATIENT_CLINIC_RESPONSE_FORMAT
            )
        );
    }


    /**
     *  @OA\Post(
     *      path="/api/clinics/",
     *      tags={"Admin"},
     *      operationId = "createClinic",
     *      summary = "create clinic",
     *      description= "Create Clinic Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={
     *                  "clinic_code",
     *                  "departement_id",
     *              },
     *              @OA\Property(property="clinic_code",type="string"),
     *              @OA\Property(property="departement_id",type="integer"),
     *          ),
     *      ),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function createClinic(ClinicForm $request) {
        $this->authorize('create' , Clinic::class);
        $validated = $request->validated();
        if ( !key_exists("departement_id", $validated) ) { 
            throw new HttpResponseException(response()->json([
                "errors" => [
                    "departement_id" => [
                        "departement_id should be supported for this end-point"
                    ]
                ]
            ] , 422));
        }
        Clinic::create($validated);
        return response()->json([
            "status" => "created",
            "data" => $validated    
        ] , 200);
    }


    /**
     *  @OA\Put(
     *      path="/api/clinics/{id}",
     *      tags={"Admin"},
     *      operationId = "updateClinic",
     *      summary = "update clinic",
     *      description= "Update Clinic Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="clinic_code",type="string"),
     *              @OA\Property(property="departement_id",type="integer"),
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
                ClinicController::PATIENT_CLINIC_RESPONSE_FORMAT
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

}
