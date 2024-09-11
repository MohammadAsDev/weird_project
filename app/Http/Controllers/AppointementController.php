<?php

namespace App\Http\Controllers;

use App\Enums\AppointementStatus;
use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Controllers\Patients\PatientController;
use App\Http\Requests\AppointementForm;
use App\Http\Requests\RoutineTestForm;
use App\Models\Appointement;
use App\Models\Doctor;
use App\Models\RoutineTest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class AppointementController extends Controller
{

    const ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT = [
        "id" => "id",
        "date" => "date",
        "period" => "period",
        "status" => "status",
        "pateint_id" => "patient_id",
        "doctor_id" => "doctor_id",
    ];

    const PATIENT_APPOINTMENT_INDEX_RESPONSE_FORMAT = [
        "id" => "id",
        "date" => "date",
        "period" => "period",
        "status" => "status",
        "doctor_id" => "doctor_id",
    ];

    const DOCTOR_APPOINTMENT_INDEX_RESPONSE_FORMAT = [
        "id" => "id",
        "date" => "date",
        "period" => "period",
        "status" => "status",
        "pateint_id" => "patient_id",
    ];

    const ADMIN_APPOINTMENT_RESPONSE_FORMAT = [
        "id" => "id",
        "date" => "date",
        "period" => "period",
        "doctor" => DoctorController::ADMIN_READ_RESPONSE_FORMAT,
        "patient" => PatientController::DOCTOR_READ_RESPONSE_FORMAT,
        "status" => "status"
    ];

    const PATIENT_APPOINTMENT_RESPONSE_FORMAT = [       // Patient's View on Appointement
        "id" => "id",
        "date" => "date",
        "period" => "period",
        "doctor" => DoctorController::PATIENT_READ_RESPONSE_FORMAT,
        "status" => "status"
    ];

    const DOCTOR_APPOINTMENT_RESPONSE_FORMAT = [        // Doctor's View on Appointement
        "id" => "id",
        "date" => "date",
        "period" => "period",
        "patient" => PatientController::DOCTOR_READ_RESPONSE_FORMAT,
        "status" => "status"
    ];


    private function getAppointementOr404($appointementId) {        
        $appointement = 
            Appointement::where("id" , $appointementId)->first();
        if ($appointement == null) {
            abort(404 , "appointement does not exist");
        }
        return $appointement;
    }

    private function formatAppointementSchedule(Collection $appointements , $n_days) {
        $periods = [];
        $start_time = 9;
        $end_time = 16;
        for ($i = $start_time ; $i < $end_time ; $i++) {
            array_push($periods , sprintf("%d-%d" , $i, $i+1));
        }

        $dates = [];
        $days = [];
        for ($i = 1; $i <= $n_days ; $i++) {
            $ith_date = Carbon::today()->addDays($i);
            array_push($dates , $ith_date->toDateString());
            array_push($days , $ith_date->dayName);
        }

        $response_data = ["working_time" => "from 9:00 to 16:00"];
        $appointements_table = [];
        foreach ($dates as $date){
            $appointements_table[$date] = [];
            foreach ($periods as $period) {
                $appointements_table[$date][$period] = false;
            }
        }


        foreach ($appointements as $appointement) {
            $appointement_date = $appointement->date->toDateString();
            if ($appointement->status == AppointementStatus::ACCEPTED) {
                $appointements_table[$appointement_date][$appointement->period] = true;                
            }
        }

        for ( $row_i = 0 ; $row_i < count($appointements_table) ; $row_i += 1 ) {
            $ith_date = $dates[$row_i];
            $ith_day = $days[$row_i];
            $response_data[$ith_date] =  [
                "date" => $ith_date,
                "day_name" => $ith_day,
                "periods" => $appointements_table[$ith_date]
            ];
        }

        return $response_data;
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
     *      operationId = "listPatientAppointements",
     *      summary = "list appointements for specific patient",
     *      description= "Patient's Appointements Endpoint.",
     *      @OA\Parameter(name="id", description="patient's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="403", description="Forbidden"),
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
     * @OA\Get(
     *      path="/api/appointements/me",
     *      tags={"Patient"},
     *      operationId = "listCurrentAppointements",
     *      summary = "list current patient's  appointements",
     *      description= "Current Patient's Appointements Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function me(Request $request) {
        $this->authorize("viewAnyAsPatient" , Appointement::class);
        $current_user = $request->user();

        if ( !$current_user->hasVerifiedEmail() ) {
            return response([
                "details" => "your email is not verified."
            ],401);
        }

        $user_id = $current_user->id;
        $appointements = Appointement::where("patient_id" , $user_id)->get();

        return response()->json($this->paginate(
            Controller::formatCollection(
                $appointements,
                AppointementController::PATIENT_APPOINTMENT_INDEX_RESPONSE_FORMAT
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
     *      @OA\Response(response="401", description="Unauthorized"),
     *  )
     */
    public function readAppointement(Request $request, $id) {
        $current_user = $request->user();
        if ( !$current_user->hasVerifiedEmail() ) {
            return response()->json([
                "details" => "your email is not verified."
            ],401);
        }
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
     *      operationId = "listPatientsAppointements",
     *      summary = "list patients' appointements for the current doctor",
     *      description= "Patients' Appointements Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="401", description="Unauthorized"),
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
                AppointementController::DOCTOR_APPOINTMENT_INDEX_RESPONSE_FORMAT
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
     *      @OA\Response(response="401", description="Unauthorized"),
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
     *                  "doctor_id",
     *                  "date",
     *                  "period",
     *              },
     *              @OA\Property(property="doctor_id",type="integer"),
     *              @OA\Property(property="date",type="date"),
     *              @OA\Property(property="period",type="string"),
     *          ),
     *      ),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *      @OA\Response(response="401", description="Unauthorized"),
     *  )
     */
    public function create(AppointementForm $request) {
        $this->authorize("create" , Appointement::class);
        $current_user = $request->user();
        if ( $current_user == null ) {
            return response()->json([
                "details" => "current user is undefined"
            ], 401);
        }

        if ( !$current_user->hasVerifiedEmail() ) {
            return response()->json([
                "details" => "your email is not verified"
            ],401);
        }

        $validated = $request->validated();

        $allocated_before_appointement = 
            Appointement::where("date" , $validated["date"])
            ->where("period" , $validated["period"])
            ->where("status" , AppointementStatus::ACCEPTED)
            ->first();

        if ( $allocated_before_appointement != null ) {
            return response()->json([
                "details" => "the requested period is reserved before"
            ] , 400);
        }


        Appointement::create(array_merge($validated , [
            "patient_id" => $request->user()->id,
        ]));
        return response()->json(["status" => "created" , "data" => $validated], 200);
    }


    /**
     *  @OA\Get(
     *      path="/api/appointements/schedule/doctors/{id}",
     *      @OA\Parameter(name="id", description="doctor's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      tags={"Patient"},
     *      operationId = "doctorSchedule",
     *      summary = "schedule doctor appointements for the next 7 days",
     *      description= "Doctor's Schedule Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="401", description="Unauthorized"),
     *  )
     */
    public function listDoctorSchedule(Request $request, $id) {  // specified for patients (list all appointements for a specific doctor)
        $this->authorize("viewAnyAsPatient" , Appointement::class);
        $current_user= $request->user();

        if ( $current_user == null ) {
            return response()->json([
                "details" => "current user is undefined"
            ],401);
        }

        if ( !$current_user->hasVerifiedEmail() ) {
            return response()->json([
                "details" => "your email is not verified."
            ],401);
        }

        $doctor = Doctor::where("user_id" , $id)->first();  
        if ($doctor == null) {
            return response()->json([
                "details" => "doctor does not exist"
            ],404);
        }


        $n_days = 7;
        $start_time = Carbon::today()->toDateString(); 
        $end_time = Carbon::today()
            ->addDays($n_days)
            ->toDateString();

        $appointements = 
            Appointement::where("doctor_id" , $id)
            ->whereBetween("date" , [$start_time , $end_time])->get();
        
        $response_data = $this->formatAppointementSchedule($appointements , $n_days);

        return response()->json($response_data);
    }


    /**
     *  @OA\Put(
     *      path="/api/appointements/me/patients/{id}",
     *      tags={"Doctor"},
     *      operationId = "updateAppointement",
     *      summary = "update appointement's status",
     *      description= "Update Appointement Endpoint.",
     *      @OA\Parameter(name="id", description="doctor's id" , in="path" , required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={
     *                  "status",
     *              },
     *              @OA\Property(property="status",type="integer"),
     *          ),
     *      ),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="400", description="Bad Request"),
     *  )
     */
    public function update(AppointementForm $request , $id) {
        $appointement = $this->getAppointementOr404($id);
        $this->authorize("update" , $appointement);

        if ( $appointement->status != AppointementStatus::NEED_ACK ) {
            return response()->json([
                "details" => "current appointement does not need acknowledgement"
            ],400);
        }

        $validated =$request->validated();

        $prev_appointement = 
            Appointement::where("date" , $appointement->date)
            ->where("period" , $appointement->period)
            ->where("status" , AppointementStatus::ACCEPTED)->first();

        if ( $prev_appointement != null && $validated["status"] == AppointementStatus::ACCEPTED->value) {
            return response()->json([
                "details" => "there's an appointement has been allocated at the same time before"
            ],400);
        }
        
        $appointement->update([
            "status" => $validated["status"]
        ]);
        return response()->json([
            "status" => "updated" , 
            "data" => 
                Controller::formatData(
                    $appointement, 
                    AppointementController::DOCTOR_APPOINTMENT_RESPONSE_FORMAT
                )
        ]);
    }


    /**
     *  @OA\Post(
     *      path="/api/appointements/me/patients/{id}/tests/",
     *      @OA\Parameter(name="id", description="patient's id" , in="path", required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )),
     *      tags={"Doctor"},
     *      operationId = "submitTest",
     *      summary = "submit appointement's Test",
     *      description= "Submit Test Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={
     *                  "breathing_rate",
     *                  "body_temperature",
     *                  "pulse_rate",
     *                  "medical_notes",
     *                  "prescription"
     *              },
     *              @OA\Property(property="breathing_rate",type="number"),
     *              @OA\Property(property="body_temperature",type="number"),
     *              @OA\Property(property="pulse_rate",type="number"),
     *              @OA\Property(property="medical_notes",type="number"),
     *              @OA\Property(property="prescription",type="number"),
     *          ),
     *      ),
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *      @OA\Response(response="404", description="Object Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *  )
     */

    public function submit(RoutineTestForm $request,$id) { //make a routine test
        $appointement = $this->getAppointementOr404($id);
        $this->authorize("update" , $appointement);

        $validated = $request->validated();

        if ( $appointement->status != AppointementStatus::ACCEPTED) {
            return response([
                "details" => "appointement is not accepted"
            ], 400);
        }
        
        $test = RoutineTest::create(array_merge(
            $validated,[
                "doctor_id" => $appointement->doctor_id,
                "patient_id" => $appointement->patient_id
            ]
        ));

        return response()->json(["status" => "created" , "data" => $test] , 200);
    }


    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
