<?php

namespace App\Http\Controllers;

use App\Enums\MedicalSpecialization;
use App\Enums\Rate;
use App\Enums\Role;
use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Controllers\Nurses\NurseController;
use App\Http\Requests\DepartementForm;
use App\Http\Requests\DoctorForm;
use App\Http\Requests\NurseForm;
use App\Models\Departement;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;

class DepartementController extends Controller
{

    public const ALL_DEPARTEMENT_RESPONSE_FORMAT = [
        "departement_name" => "name",
        "specialization" =>  "specialization",
        "description" => "description",
        "structured" => true
    ];

    private function getUserData($request_data) {
        $user_data = Arr::except(
            $request_data , 
            [
                "departement_id" , 
                'rate' ,
                'specialization' , 
                "short_description"
            ]
        );
        return $user_data;
    }

    private function getDoctordData($request_data) {
        return [
            "rate" => $request_data['rate'],
            'specialization' => $request_data['specialization'],
            'short_description' => $request_data['short_description']
        ];
    }

    private function getNuresData($request_data) {
        return [
            "rate" => $request_data['rate'],
            'short_description' => $request_data['short_description']
        ];
    }

    /**
     * @OA\Post(
     *     path="/api/departements",
     *     @OA\Response(response="200", description="Create new departement"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="422", description="Invalid data"),
     * )
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
     * @OA\Get(
     *     path="/api/departements/{id}",
     *     @OA\Parameter(name="id", description="departement's id" , in="path"),
     *     @OA\Response(response="200", description="Read a specifc departement"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Nurse does not exist"),
     * )
     */
    protected function read($id) {
        $departement = Departement::where('id' , $id)->first();
        if ( $departement == null ) {
            return response()->json([
                "details" => "departement does not exist"
            ],404);       
        }
        $this->authorize('view' , $departement);
        return response()->json($departement, 200);
    }


    /**
     * @OA\Put(
     *     path="/api/departements/{id}",
     *     @OA\Parameter(name="id", description="departement's id" , in="path"),
     *     @OA\Response(response="200", description="Update a specifc departement"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Departement does not exist"),     
     *     @OA\Response(response="422", description="Invalid data")
     * )
     */
    protected function update(DepartementForm $request , $id) {
        $departement = Departement::where('id' , $id)->first();
        if ( $departement == null ){
            return response()->json([
                "details" => "departement does not exist"
            ],404); 
        }
        $this->authorize('update' , $departement);

        $validated = $request->validated();
        $departement->update($validated);

        return response()->json($departement, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/departements/{id}",
     *     @OA\Parameter(name="id", description="departement's id" , in="path"),
     *     @OA\Response(response="204", description="Delete a specific departement"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Departement does not exist"), 
     * )
     */
    protected function delete($id) {
        $departement = Departement::where('id' , $id)->first();
        if ( $departement == null) {
            return response()->json([
                "details" => "departement does not exist"
            ],404);
        }
        $this->authorize('delete' , $departement);

        $departement->delete();
        return response()->json([], 204);
    }


    /**
     * @OA\GET(
     *     path="/api/departements/",
     *     @OA\Response(response="200", description="List all departements"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    protected function index(){
        $this->authorize("viewAny" , Departement::class);
        $departements = Departement::all();
        return response()->json(
            $this->paginate($departements)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/departements/{id}/doctors/",
     *     @OA\Response(response="200", description="Create a doctor in a specifc departement"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Departement does not exist"),
     * )
     */
    protected function createDoctor(DoctorForm $request , $id) {
        $departement = Departement::where("id" , $id)->first();
        if ( $departement == null ) {
            return response()->json([
                "details" => "departement does not exist"
            ],404);
        }
        $this->authorize("create" , Doctor::class);
        $validated = $request->validated();
        
        $doctor_user = User::create(array_merge(
            $this->getUserData($validated) , 
            ["role_id" => Role::DOCTOR]
        ));
        Doctor::create(array_merge(
            $this->getDoctordData($validated) , 
            ["departement_id" => $id , "user_id" => $doctor_user->id]
        ));
        return response()->json([
            "status" => "created",
            "data" => $validated
        ] , 200);
    }

    /**
     * @OA\Get(
     *     path="/api/departements/{id}/doctors/",
     *     @OA\Response(response="200", description="List all doctors in a specifc departement"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Departement does not exist"),
     * )
     */
    protected function listDoctors($id) {
        $departement = Departement::where("id" , $id)->first();
        if ( $departement == null ) {
            return response()->json([
                "details" => "departement does not exist"
            ],404);
        }
        $this->authorize("viewAny" , Doctor::class);

        $doctors = Doctor::where("departement_id" , $id)->get();
        $doctors_data = [];
        foreach ($doctors as $doctor) {
            array_push($doctors_data , Controller::formatData($doctor , DoctorController::ADMIN_INDEX_RESPONSE_FORMAT));
        }
        return response()->json([
            $this->paginate($doctors_data)
        ] , 200);
    }

    /**
     * @OA\Post(
     *     path="/api/departements/{id}/nurses/",
     *     @OA\Response(response="200", description="Create a nurse in a specifc departement"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Departement does not exist"),
     * )
     */
    protected function createNurse(NurseForm $request , $id) {
        $departement = Departement::where("id" , $id)->first();
        if ( $departement == null ) {
            return response()->json([
                "details" => "departement does not exist"
            ],404);
        }
        $this->authorize("create" , Nurse::class);
        $validated = $request->validated();

        $user = User::create(array_merge($this->getUserData($validated) , ["role_id" => Role::NURSE]));
        Nurse::create(array_merge($this->getNuresData($validated) , ["departement_id" => $id , "user_id" => $user->id]));
        return response()->json([
            "status" => "created",
            "data" => $validated
        ] , 200);
    }

    /**
     * @OA\Get(
     *     path="/api/departements/{id}/nurses/",
     *     @OA\Response(response="200", description="List all nurses in a specifc departement"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Departement does not exist"),
     * )
     */
    protected function listNurses($id) {
        $departement = Departement::where("id" , $id)->first();
        if ( $departement == null ) {
            return response()->json([
                "details" => "departement does not exist"
            ],404);
        }
        $this->authorize("viewAny" , Nurse::class);

        $nurses = Nurse::where("departement_id" , $id)->get();
        $nurses_data = [];
        foreach ($nurses as $nurse) {
            array_push($nurses_data , Controller::formatData($nurse , NurseController::ADMIN_INDEX_RESPONSE_FORMAT));
        }
        return response()->json([
            $this->paginate($nurses_data)
        ] , 200);
    }

    
    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {

        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);

    }
}
