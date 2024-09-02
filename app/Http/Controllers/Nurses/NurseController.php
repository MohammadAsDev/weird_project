<?php

namespace App\Http\Controllers\Nurses;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Doctors\DoctorController;
use App\Http\Requests\NurseForm;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;

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

    /**
     * @OA\Post(
     *     path="/api/nurses",
     *     @OA\Response(response="200", description="Create new nurse"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="422", description="Invalid data"),
     * )
     */
    protected function create(NurseForm $request) {
        $this->authorize('create' , Nurse::class);
        
        $validated = $request->validated();
        $user_data = Arr::except($validated , ['rate' , 'short_description']);
        $user_data = array_merge($user_data , ["role_id" => Role::NURSE->value]);

        $user = User::create($user_data);
        Nurse::create([
            'user_id' => $user->id,
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
     *     path="/api/nurses/{id}",
     *     @OA\Parameter(name="id", description="nurse's id" , in="path"),
     *     @OA\Response(response="200", description="Read a specifc nurse"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Nurse does not exist"),
     * )
     */
    protected function read($id) {
        $nurse = Nurse::where('user_id' , $id)->first();
        if ( $nurse == null ) {
            return response()->json([
                "details" => "nurse account does not exist"
            ],404);       
        }
        $this->authorize('view' , $nurse);
        return response()->json(
            Controller::formatData($nurse , NurseController::ADMIN_READ_RESPONSE_FORMAT)
            , 200
        );
    }


    /**
     * @OA\Put(
     *     path="/api/nurses/{id}",
     *     @OA\Parameter(name="id", description="nurse's id" , in="path"),
     *     @OA\Response(response="200", description="Update a specifc nurse"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Nurse does not exist"),     
     *     @OA\Response(response="422", description="Invalid data")
     * )
     */
    protected function update(NurseForm $request , $id) {
        $nurse = Nurse::where('user_id' , $id)->first();
        if ( $nurse == null ){
            return response()->json([
                "details" => "nurse account does not exist"
            ],404); 
        }
        $this->authorize('update' , $nurse);

        $validated = $request->validated();
        $user_data = Arr::except($validated , ['rate' , 'short_description']);

        $nurse->user->update($user_data);
        $nurse->update([
            "rate" => $validated["rate"] ?? $nurse->rate,
            "short_description" => $validated["short_description"] ?? $nurse->short_description
        ]);


        return response()->json(
            Controller::formatData($nurse , NurseController::ADMIN_READ_RESPONSE_FORMAT),
             200
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/nurses/{id}",
     *     @OA\Parameter(name="id", description="nurse's id" , in="path"),
     *     @OA\Response(response="204", description="Delete a specific nurse"),
     *     @OA\Response(response="403", description="Not authorized"),
     *     @OA\Response(response="404", description="Nurse does not exist"), 
     * )
     */
    protected function delete($id) {
        $nurse = Nurse::where('user_id' , $id)->first();
        if ( $nurse == null) {
            return response()->json([
                "details" => "nurse does not exist"
            ],404);
        }
        $this->authorize('delete' , $nurse);

        $user = $nurse->user;
        $nurse->delete();
        $user->delete();
        return response()->json([], 204);
    }


    /**
     * @OA\GET(
     *     path="/api/nurses/",
     *     @OA\Response(response="200", description="List all nurses"),
     *     @OA\Response(response="403", description="Not authorized"),
     * )
     */
    protected function index(){
        $this->authorize("viewAny" , Nurse::class);
        $nurses = Nurse::all();
        $nurses_response = [];

        foreach($nurses as $nurse) {
            $nurse_data = Controller::formatData(
                $nurse , 
                NurseController::ADMIN_INDEX_RESPONSE_FORMAT
            );
            array_push($nurses_response, $nurse_data);
        }
        
        return response()->json(
            $this->paginate($nurses_response)
        );
    }

    protected function doctor($id) {
        $this->authorize("viewAny" , Doctor::class);
        $nurse = Nurse::where("user_id" , $id)->first();
        if ( $nurse == null ) {
            return response()->json([
                "details" => "nurse does not exist"
            ],404);
        }
        
        return response()->json(
            Controller::formatData(
                $nurse->doctor , 
                DoctorController::ADMIN_READ_RESPONSE_FORMAT
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

