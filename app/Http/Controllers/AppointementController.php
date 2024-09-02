<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Controllers\Patients\PatientController;
use App\Http\Requests\AppointementForm;
use App\Models\Appointement;
use App\Models\Clinic;
use App\Models\Doctor;
use Carbon\Carbon;
use Error;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;

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

    private function checkDateValidation($appointements_list , $date) { // need more work
        // $date = date_create($date);
        $date = Carbon::createFromFormat("Y-m-d H:m:s" , $date);
        foreach ($appointements_list as $appointement) {
            $appointement_date = Carbon::createFromFormat("Y-m-d H:m:S.i" , $appointement["date"]);
            $diff = $date->diffInMinutes($appointement_date);
            if ( $diff <= 60 ) {
                return false;
            }
        }
        return true;
    }


    /**
     * @OA\Get(
     *     path="/api/appointments",
     *     tags: ["Admin"],
     *     @OA\Response(response="200", description="List all apointements"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function index() {
        $this->authorize("viewAny" , Appointement::class);
        $appointements = Appointement::all();
        $repsonse_data = [];
        foreach ( $appointements as $appointement ) {
            array_push(
                $repsonse_data ,
                Controller::formatData(
                    $appointement , 
                    AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT,
                )
            );
        }
        return response()->json($this->paginate($repsonse_data));
    }


    /**
     * @OA\Get(
     *     path="/api/appointements/me",
     *     tags: ["Patient"],
     *     @OA\Response(response="200", description="List all appointments for the current patient"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function me(Request $request) {
        $this->authorize("viewAsPatient" , Appointement::class);
        $patient_id = $request->user()->id;
        $appointements = Appointement::where("patient_id" , $patient_id)->get();
        $repsonse_data = [];
        foreach ( $appointements as $appointement ) {
            array_push(
                $repsonse_data ,
                Controller::formatData(
                    $appointement , 
                    AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT,
                )
            );
        }
        return response()->json($this->paginate($repsonse_data));
    }

    /**
     * @OA\Get(
     *     path="/api/appointements/me/{id}",
     *     tags: ["Patient"],
     *     @OA\Response(response="200", description="Get a specific appointment for the current patient"),
     *     @OA\Response(response="404", description="Appointement does not exist"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function readAppointement(Request $request , $id) {
        $this->authorize("viewAsPatient" , Appointement::class);
        $patient_id = $request->user()->id;
        
        $appointement = 
            Appointement::
            where("patient_id" , $patient_id)
            ->where("id" , $id)
            ->first();
        
        if ($appointement == null) {
            return response()->json([
                "details" => "appointement does not exist"
            ],404);
        }
        return response()->json(
            Controller::formatData(
                $appointement,
                AppointementController::PATIENT_APPOINTMENT_RESPONSE_FORMAT
            )
        );
    }


    /**
     * @OA\Get(
     *     path="/api/appointements/patients",
     *     tags: ["Doctor"],
     *     @OA\Response(response="200", description="List all appointements for the current doctor"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function patients(Request $request) {
        $this->authorize("viewAsDoctor" , Appointement::class);
        $doctor_id = $request->user()->id;
        $appointements = Appointement::where("doctor_id" , $doctor_id)->get();
        $repsonse_data = [];
        foreach ( $appointements as $appointement ) {
            array_push(
                $repsonse_data ,
                Controller::formatData(
                    $appointement , 
                    AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT,
                )
            );
        }
        return response()->json($this->paginate($repsonse_data));
    }

    /**
     * @OA\Get(
     *     path="/api/appointements/patients/{id}",
     *     tags: ["Doctor"],
     *     @OA\Response(response="404", description="Patient does not exist"),
     *     @OA\Response(response="200", description="Read a specific appointement for the current doctor"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function readPatient(Request $request , $id) {
        $this->authorize("viewAsDoctor" , Appointement::class);
        $doctor_id = $request->user()->id;
        
        $appointement = 
            Appointement::
            where("doctor_id" , $doctor_id)->
            where("id" , $id)->
            first();

        if ( $appointement == null ){
            return response()->json([
                "details" => "patient does not exist"
            ],404);
        } 
        
        return response()->json(
            Controller::formatData(
                $appointement,
                AppointementController::DOCTOR_APPOINTMENT_RESPONSE_FORMAT
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/appointements/",
     *     tags: ["Patient"],
     *     @OA\Response(response="200", description="Create a new appointement"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="422", description="Invalid data"),
     * )
     */
    public function create(AppointementForm $request) { // need validation
        $this->authorize("create" , Appointement::class);
        $validated = $request->validated();
        $appointements = Appointement::where(
            "date",
            ">",
            Carbon::now()->today()->toDateTimeString()
        )->get()->toArray();
        if ( ! $this->checkDateValidation($appointements , $validated["date"]) ) {
            return response()->json([
                "details" => "invalid appointement's date"
            ],422);
        }
        Appointement::create(array_merge($validated , ["patient_id" => $request->user()->id]));
        return response()->json(["status" => "created" , "data" => $validated], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/appointements/clinics/{id}",
     *     @OA\Parameter(name="id", description="clinic's id" , in="path"),
     *     tags: ["Patient"],
     *     @OA\Response(response="200", description="List all appointements for a specific clinic"),
     *     @OA\Response(response="404", description="Clinic does not exist"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function listClinicAppointements(Request $request, $id) {  // specified for patients (list all appointements for a specific clinic)
        $this->authorize("ViewAsPatient" , Appointement::class);
        $clinic = Clinic::where("id" , $id)->first();
        if ($clinic == null) {
            return response()->json([
                "details" => "clinic does not exist"
            ],404);
        }

        $period = strtolower(htmlentities($request->query("period" , "month")));
        $start_end_dates = $this->getStartEndDate($period);
        
        $apointements = Appointement::where("clinic_id" , $id)->whereBetween("date" , $start_end_dates)->get();
        
        $response_data = [];
        foreach ($apointements as $appointement) {
            array_push($response_data ,
                Controller::formatData(
                    $appointement ,
                     AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT
                )
            );
        }
        return response()->json($this->paginate($response_data));
    }


    /**
     * @OA\Get(
     *     path="/api/appointements/doctors/{id}",
     *     @OA\Parameter(name="id", description="doctor's id" , in="path"),
     *     tags: ["Patient"],
     *     @OA\Response(response="200", description="List all appointements for a specific doctor"),
     *     @OA\Response(response="404", description="Doctor does not exist"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    public function listDoctorAppointements(Request $request, $id) {  // specified for patients (list all appointements for a specific doctor)
        $this->authorize("ViewAsPatient" , Appointement::class);

        $doctor = Doctor::where("user_id" , $id)->first();  
        if ($doctor == null) {
            return response()->json([
                "details" => "doctor does not exist"
            ],404);
        }

        $period = strtolower(htmlentities($request->query("period" , "month")));
        $start_end_dates = $this->getStartEndDate($period);
        error_log($start_end_dates[0]);
        error_log($start_end_dates[1]);

        $apointements = Appointement::where("doctor_id" , $id)->whereBetween("date" , $start_end_dates)->get();
        
        $response_data = [];
        foreach ($apointements as $appointement) {
            array_push($response_data ,
                Controller::formatData(
                    $appointement ,
                     AppointementController::ALL_APPOINTMENT_INDEX_RESPONSE_FORMAT
                )
            );
        }
        return response()->json($this->paginate($response_data));
    }

    /**
     * @OA\Put(
     *     path="/api/appointements/patients/{id}",
     *     @OA\Parameter(name="id", description="clinic's id" , in="path"),
     *     tags: ["Doctor"],
     *     @OA\Response(response="200", description="sumbit appointement status"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="422", description="Invalid data"),
     *     @OA\Response(response="404", description="Patient does not exist"),
     * )
     */
    public function submit(AppointementForm $request,$id) {
        $appointement = Appointement::where("id" , $id)->first();
        if ( $appointement == null ) {
            return response()->json([
                "details" => "appointement does not exist"
            ],404);
        }
        $this->authorize("update" , $appointement);

        $appointements_list = Appointement::where(
            "date",
            ">",
            Carbon::now()->today()->toDateTimeString()
        )->get()->toArray();


        $validated = $request->validated();
    
        if ( ! $this->checkDateValidation($appointements_list , $validated["next_date"]) ) {
            return response()->json([
                "details" => "invalid next date"
            ],422);
        }

        $appointement->update($validated);

        Appointement::create([
            "doctor_id" => $appointement->doctor_id,
            "clinic_id" => $appointement->clinic_id,
            "date" => $validated["next_date"]
        ]);

        return response()->json([
            "details" => "a new date has been set",
            Controller::formatData(
                $appointement,
                AppointementController::DOCTOR_APPOINTMENT_RESPONSE_FORMAT
            )
        ],200);
    }


    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
