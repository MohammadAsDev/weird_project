<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorForm;
use App\Enums\Role;
use App\Http\Controllers\Nurses\NurseController;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;

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

    /**
     * @OA\Post(
     *     path="/api/doctors",
     *     tags={"Admin"},
     *     @OA\Response(response="200", description="Create new doctor"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="422", description="Invalid data"),
     * )
     */
    protected function create(DoctorForm $request) {
        $this->authorize('create' , Doctor::class);
        
        $validated = $request->validated();
        $user_data = Arr::except($validated , ['specialization' , 'rate' , 'short_description']);
        $user_data = array_merge($user_data , ["role_id" => Role::DOCTOR->value]);

        $user = User::create($user_data);
        Doctor::create([
            'user_id' => $user->id,
            'specialization' => $validated['specialization'],
            'rate' => $validated['rate'],
            'short_description' => $validated['short_description']
        ]);

        
        return response()->json([
            "status" => "created",
            "result" => $validated
        ], 200);
    }

    
    /**
     * @OA\Get(
     *     path="/api/doctors/{id}",
     *     tags={"Admin"},
     *     @OA\Parameter(name="id" , description="doctor's id" , in="path"),
     *     @OA\Response(response="200", description="Read a specific doctor"),
     *     @OA\Response(response="403", description="Not Authorized"),
     *     @OA\Response(response="404", description="Doctor does not exist")
     * )
     */
    protected function read($id) {
        $doctor = Doctor::where('user_id' , $id)->first();
        if ( $doctor == null ) {
            return response()->json([
                "details" => "doctor account does not exist"
            ],404);       
        }
        $this->authorize('view' , $doctor);
        return response()->json(
            Controller::formatData(
                $doctor ,
                DoctorController::ADMIN_READ_RESPONSE_FORMAT
            ),
         200);
    }

    /**
     * @OA\Put(
     *     path="/api/doctors/{id}",
     *     tags={"Admin"},
     *     @OA\Parameter(name="id" , description="doctor's id" , in="path"),
     *     @OA\Response(response="200", description="Update a specific doctor"),
     *     @OA\Response(response="403", description="Not Authorized"),
     *     @OA\Response(response="404", description="Doctor does not exist"),
     *     @OA\Response(response="422", description="Invalid data")
     * )
     */
    protected function update(DoctorForm $request , $id) {
        $doctor = Doctor::where('user_id' , $id)->first();
        if ( $doctor == null ){
            return response()->json([
                "details" => "doctor account does not exist"
            ],400); 
        }
        $this->authorize('update' , $doctor);

        $validated = $request->validated();
        $user_data = Arr::except($validated , ['specialization' , 'rate' , 'short_description']);

        $doctor->user->update($user_data);
        $doctor->update([
            "specialization" => $validated['specialization'] ?? $doctor->specialization,
            "rate" => $validated["rate"] ?? $doctor->rate,
            "short_description" => $validated["short_description"] ?? $doctor->short_description
        ]);


        return response()->json(
            Controller::formatData(
                $doctor ,
                DoctorController::ADMIN_READ_RESPONSE_FORMAT
            ), 200
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/doctors/{id}",
     *     tags={"Admin"},
     *     @OA\Parameter(name="id" , description="doctor's id" , in="path"),
     *     @OA\Response(response="204", description="Delete a specific doctor"),
     *     @OA\Response(response="403", description="Not Authorized"),
     *     @OA\Response(response="404", description="Doctor does not exist")
     * )
     */
    protected function delete($id) {
        $doctor = Doctor::where('user_id' , $id)->first();
        if ( $doctor == null) {
            return response()->json([
                "details" => "doctor does not exist"
            ],400);
        }
        $this->authorize('delete' , $doctor);

        $user = $doctor->user;
        $doctor->delete();
        $user->delete();
        return response()->json([], 204);
    }

    /**
     * @OA\Get(
     *     path="/api/doctors/",
     *     tags={"Admin"},
     *     @OA\Response(response="200", description="List all doctors"),
     *     @OA\Response(response="403", description="Not Authorized")
     * )
     */
    protected function index(){
        $this->authorize("viewAny" , Doctor::class);
        $doctors = Doctor::all();
        $doctors_response = [];
        foreach($doctors as $doctor) {
            $doctor_data = Controller::formatData($doctor , DoctorController::ADMIN_INDEX_RESPONSE_FORMAT);
            array_push($doctors_response, $doctor_data);
        }
        return response()->json(
            $this->paginate(collect($doctors_response))
        );
    }

    /**
     * @OA\Get(
     *     path="/api/doctors/{id}/nurses",
     *     tags={"Admin"},
     *     @OA\Parameter(name="id" , description="doctor's id" , in="path"),
     *     @OA\Response(response="200", description="List all nurses working with a specific doctor"),
     *     @OA\Response(response="404", description="doctor does not exist"),
     *     @OA\Response(response="403", description="Not Authorized")
     * )
     */
    protected function listNurses($id) {
        $doctor = Doctor::where("user_id" , $id)->first();
        if ( $doctor == null ) {
            return response()->json([
                "details" => "doctor does not exist"
            ] , 404);
        }
        $this->authorize("viewNurses", $doctor);
        $nurses = $doctor->nurses;
        $nurses_data = [];
        foreach ($nurses as $nurse) {
            array_push(
                $nurses_data, 
                Controller::formatData(
                    $nurse , 
                    NurseController::ADMIN_INDEX_RESPONSE_FORMAT
                
                )
            );
        }
        return response()->json($this->paginate($nurses_data));
    }

    /**
     * @OA\Get(
     *     path="/api/doctors/{id}/appointements",
     *     tags={"Admin" , "Doctor"},
     *     @OA\Parameter(name="id" , description="doctor's id" , in="path"),
     *     @OA\Response(response="200", description="List all appointement for a specific doctor"),
     *     @OA\Response(response="404", description="doctor does not exist"),
     *     @OA\Response(response="403", description="Not Authorized")
     * )
     */
    public function appointements($id) {
        $doctor = Doctor::where("user_id" , $id)->first();
        if ( $doctor == null ) {
            return response()->json([
                "details" => "doctor does not exist"
            ] , 404);
        }
        $this->authorize('viewAppointements' , $doctor);
        return response()->json($this->paginate($doctor->appointements));
    }

    public function paginate($items, $perPage = 5, $page = null, $options = []) {

        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);

    }
}
