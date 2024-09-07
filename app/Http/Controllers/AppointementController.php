<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Controllers\Patients\PatientController;
use App\Http\Requests\AppointementForm;
use App\Models\Appointement;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RoutineTest;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AppointementController extends Controller
{

    const ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT = [
        "id" => "id",
        "date" => "date",
        "next_date" => "next_date",
        "status" => "status"
    ];

    const ADMIN_APPOINTMENT_RESPONSE_FORMAT = [
        "id" => "id",
        "date" => "date",
        "next_date" => "next_date",
        "doctor" => DoctorController::ADMIN_READ_RESPONSE_FORMAT,
        "clinic" => ClinicController::PATIENT_CLINIC_ONLY_RESPONSE_FOMAT,
        "status" => "status"
    ];

    const PATIENT_APPOINTMENT_RESPONSE_FORMAT = [
        "id" => "id",
        "date" => "date",
        "next_date" => "next_date",
        "doctor" => DoctorController::PATIENT_READ_RESPONSE_FORMAT,
        "clinic" => ClinicController::PATIENT_CLINIC_ONLY_RESPONSE_FOMAT,
        "status" => "status"
    ];

    const DOCTOR_APPOINTMENT_RESPONSE_FORMAT = [
        "id" => "id",
        "date" => "date",
        "next_date" => "next_date",
        "patient" => PatientController::DOCTOR_READ_RESPONSE_FORMAT,
        "clinic" => ClinicController::PATIENT_CLINIC_ONLY_RESPONSE_FOMAT,
        "status" => "status"
    ];

    private function getStartEndDate($period) {
        $start = Carbon::now()->today();
        $end = Carbon::now()->today();

        switch ($period) {
            case "month":
                $start = $start->startOfMonth();
                $end = $end->endOfMonth();
                break;
            case "year":
                $start = $start->startOfYear();
                $end = $end->endOfYear();
                break;
            default:
                $start = $start->startOfWeek();
                $end = $end->endOfWeek();
                break;
        }

        $start = $start->toDateTimeString();
        $end = $end->toDateTimeString();

        return [$start, $end];
    }



    private function getAppointementOr404($appointementId) {        
        $appointement = 
            Appointement::where("id" , $appointementId)->first();
        if ($appointement == null) {
            abort(404 , "appointement does not exist");
        }
        return $appointement;
    }

    /**
     *  @OA\Get(
     *      path="/api/appointments",
     *      tags= {"Admin"},
     *      operationId = "listAppointements",
     *      summary = "list all appointements",
     *      description= "List Appointements Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function index() {
        $this->authorize("viewAny" , Appointement::class);
        $appointements = Appointement::all();
        return response()->json($this->paginate(
            Controller::formatCollection(
                $appointements, 
                AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT
            )
        ));
    }

    /**
     *  @OA\Get(
     *      path="/api/appointements/doctors/{id}/",
     *      tags={"Admin"},
     *      operationId = "listDoctorAppointements",
     *      summary = "list doctor's appointements",
     *      description= "Doctor's Appointements Endpoint.",
     *      @OA\Parameter(name="id" , description="doctor's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="403", description="Not Authorized")
     *  )
     */
    public function listDoctorAppointements($id) {
        $this->authorize('viewAny' , Doctor::class);

        $doctor = DoctorController::getDoctorOr404($id);
        $appointements = $doctor->appointements;
        return response()->json(
            $this->paginate(
                Controller::formatCollection(
                    $appointements,
                    AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT
                )
            )
        );
    }

    /**
     *  @OA\Get(
     *      path="/api/appointements/patients/{id}/",
     *      tags={"Admin"},
     *      operationId = "readPatientAppointements",
     *      summary = "list patient's appointements",
     *      description= "Patient's Appointements Endpoint.",
     *      @OA\Parameter(name="id", description="patient's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *  )
     */
    public function listPatientAppointements($id) {
        $this->authorize("viewAny" , Appointement::class);
        $patient = PatientController::getPatientOr404($id);
        $appointements = $patient->appointements;
        return response()->json($this->paginate(
            Controller::formatCollection(
                $appointements,
                AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT
            )
        ));
    }


    /**
     *  @OA\Get(
     *      path="/api/appointements/clinics/{id}/",
     *      tags={"Admin"},
     *      operationId = "listClinicAppointements",
     *      summary = "list clinic's appointments",
     *      description= "Clinic's Appointement Endpoint.",
     *      @OA\Parameter(name="id", description="clinic's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    public function listClinicAppointements($id) {
        $this->authorize("viewAny" , Appointement::class);
        $clinic = ClinicController::getClinicOr404($id);
        $appointements = $clinic->appointements;

        return response()->json($this->paginate(
            Controller::formatCollection(
                $appointements,
                AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT
            )
        ));
    }


    /**
     * @OA\Get(
     *      path="/api/appointements/me",
     *      tags={"Patient"},
     *      operationId = "listCurrentAppointements",
     *      summary = "list current user's  appointements",
     *      description= "Current Appointements Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     * )
     */
    public function me(Request $request) {
        $this->authorize("viewAnyAsPatient" , Appointement::class);
        $patient_id = $request->user()->id;
        $appointements = Appointement::where("patient_id" , $patient_id)->get();

        return response()->json($this->paginate(
            Controller::formatCollection(
                $appointements,
                AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT
            )
        ));
    }

    /**
     *  @OA\Get(
     *      path="/api/appointements/me/{id}",
     *      tags={"Patient"},
     *      @OA\Parameter(name="id", description="appointement's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      operationId = "readCurrentAppointements",
     *      summary = "read current user's  appointement",
     *      description= "Read  Current Appointement Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function readAppointement($id) {
        $appointement = $this->getAppointementOr404($id);
        $this->authorize("viewAsPatient" , $appointement);
        
        return response()->json(
            Controller::formatData(
                $appointement,
                AppointementController::PATIENT_APPOINTMENT_RESPONSE_FORMAT
            )
        );
    }


    /**
     *  @OA\Get(
     *      path="/api/appointements/patients",
     *      tags={"Doctor"},
     *      operationId = "listPatientAppointements",
     *      summary = "list patients appointements",
     *      description= "Patients Appointement Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function patients(Request $request) {
        $this->authorize("viewAnyAsDoctor" , Appointement::class);
        $doctor_id = $request->user()->id;
        $appointements = Appointement::where("doctor_id" , $doctor_id)->get();

        return response()->json($this->paginate(
            Controller::formatCollection(
                $appointements,
                AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT
            )
        ));
    }

    /**
     *  @OA\Get(
     *      path="/api/appointements/me/patients/{id}",
     *      tags={"Doctor"},
     *       @OA\Parameter(name="id", description="appointement's id" , in="path", required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      operationId = "readPatientAppointement",
     *      summary = "read patient's appointement",
     *      description= "Read Patient Appointement Endpoint.",
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function readPatient($id) { 
        $appointement = $this->getAppointementOr404($id);
        $this->authorize("viewAsDoctor" , $appointement);
    
        return response()->json(
            Controller::formatData(
                $appointement,
                AppointementController::DOCTOR_APPOINTMENT_RESPONSE_FORMAT
            )
        );
    }

    /**
     *  @OA\Post(
     *      path="/api/appointements/",
     *      tags={"Patient"},
     *      operationId = "createAppointement",
     *      summary = "create appointement",
     *      description= "Create Appointement Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={
     *                  "clinic_id",
     *                  "date",
     *              },
     *              @OA\Property(property="clinic_id",type="integer"),
     *              @OA\Property(property="date",type="date"),
     *          ),
     *      ),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *  )
     */
    public function create(AppointementForm $request) {
        $this->authorize("create" , Appointement::class);
        $validated = $request->validated();
        $clinic = Clinic::where("id" , $validated["clinic_id"])->first();

        Appointement::create(array_merge($validated , [
            "patient_id" => $request->user()->id,
            "doctor_id" => $clinic->doctor->user_id
        ]));
        return response()->json(["status" => "created" , "data" => $validated], 200);
    }


    /**
     *  @OA\Get(
     *      path="/api/appointements/schedule/clinics/{id}",
     *      @OA\Parameter(name="id", description="clinic's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Parameter(name="period", description="appointement's period" , in="query",
     *          @OA\Schema(
     *              type="string"
     *          )),
     *      tags={"Patient"},
     *      operationId = "clinicSchedule",
     *      summary = "schedule clinic appointements",
     *      description= "Clinic Schedule Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function listClinicSchedule(Request $request, $id) {  // specified for patients (list all appointements for a specific clinic)
        $this->authorize("viewAnyAsPatient" , Appointement::class);
        $clinic = Clinic::where("id" , $id)->first();
        if ($clinic == null) {
            return response()->json([
                "details" => "clinic does not exist"
            ],404);
        }

        $period = strtolower(htmlentities($request->query("period" , "month")));
        $start_end_dates = $this->getStartEndDate($period);
        
        $appointements = Appointement::where("clinic_id" , $id)->whereBetween("date" , $start_end_dates)->get();

        return response()->json($this->paginate(
            Controller::formatCollection(
                $appointements,
                AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT
            )
        ));
    }


    /**
     *  @OA\Get(
     *      path="/api/appointements/schedule/doctors/{id}",
     *      @OA\Parameter(name="id", description="doctor's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Parameter(name="period", description="appointement's period" , in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      tags={"Patient"},
     *      operationId = "doctorSchedule",
     *      summary = "schedule doctor appointements",
     *      description= "Doctor Schedule Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="403", description="Forbidden"),
     *  )
     */
    public function listDoctorSchedule(Request $request, $id) {  // specified for patients (list all appointements for a specific doctor)
        $this->authorize("viewAsPatient" , Appointement::class);

        $doctor = Doctor::where("user_id" , $id)->first();  
        if ($doctor == null) {
            return response()->json([
                "details" => "doctor does not exist"
            ],404);
        }

        $period = strtolower(htmlentities($request->query("period" , "month")));
        $start_end_dates = $this->getStartEndDate($period);

        $appointements = Appointement::where("doctor_id" , $id)->whereBetween("date" , $start_end_dates)->get();
        
        return response()->json($this->paginate(
            Controller::formatCollection(
                $appointements,
                AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT
            )
        ));
    }

    /**
     *  @OA\Put(
     *      path="/api/appointements/me/patients/{id}/submit",
     *      @OA\Parameter(name="id", description="patient's id" , in="path", required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      tags={"Doctor"},
     *      operationId = "submitSchedule",
     *      summary = "submit appointement's status",
     *      description= "Submit Appointement Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={
     *                  "next_date",
     *                  "status",
     *              },
     *              @OA\Property(property="next_date",type="date"),
     *              @OA\Property(property="status",type="integer" ,enum=App\Enums\AppointementStatus::class),
     *          ),
     *      ),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *  )
     */
    public function submit(AppointementForm $request,$id) {
        $appointement = $this->getAppointementOr404($id);
        $this->authorize("update" , $appointement);

        $validated = $request->validated();

        $appointement_info = Arr::except($validated , ["attachement"]);        
        $appointement->update($appointement_info);

        $response_data = [];
        $status_code = 0;
        DB::beginTransaction();

        try {
            Appointement::create([
                "patient_id" => $appointement->patient_id,
                "doctor_id" => $appointement->doctor_id,
                "clinic_id" => $appointement->clinic_id,
                "date" => $validated["next_date"]
            ]);
    
            RoutineTest::create(array_merge(
                $validated["attachement"],[
                    "doctor_id" => $appointement->doctor_id,
                    "patient_id" => $appointement->patient_id
                ]
            ));

            DB::commit();

            $status_code = 200;
            $response_data = [
                "status" => "a new date has been set",
                "data" => Controller::formatData(
                    $appointement,
                    AppointementController::DOCTOR_APPOINTMENT_RESPONSE_FORMAT
                )
            ];
        } catch (Exception $exp) {
            DB::rollBack();
            $status_code = 500;
            $response_data = ["status" => "failed to save the changes"];
        }


        return response()->json($response_data , $status_code);
    }


    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
