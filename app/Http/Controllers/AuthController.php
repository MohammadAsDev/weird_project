<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginForm;

class AuthController extends Controller {

    public function __construct() {
        # By default we are using here auth:api middleware
        $this->middleware('auth:api', ['except' => ['login' , 'register']]);
    }


    /**
     *  @OA\Post(
     *       path="/api/auth/login",
     *       tags={"Doctor" , "Patient" , "Nurse" , "Admin"},
     *       operationId = "login",
     *       summary = "log into the system",
     *       description= "Login Endpoint.",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="email",type="string"),
     *              @OA\Property(property="password",type="string"),
     *      )),
     *       @OA\Response(response="200", description="OK"),
     *       @OA\Response(response="401", description="Not Authorized"),
     *  )
     */
    public function login(LoginForm $request) {
        $validated = $request->validated();
        if (! $token = auth()->attempt($validated)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token); # If all credentials are correct - we are going to generate a new access token and send it back on response
   }


    /**
     *  @OA\Post(
     *       path="/api/auth/logout",
     *       tags={"Doctor" , "Patient" , "Nurse" , "Admin"},
     *       operationId = "logout",
     *       summary = "log out from system",
     *       description= "Logout Endpoint.",
     *       @OA\Response(response="200", description="OK"),
     *  )
     */
    public function logout() {
        auth()->logout(); # This is just logout function that will destroy access token of current user
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function confirm() {
        
    }

    /**
     *  @OA\Post(
     *       path="/api/auth/refresh",
     *       tags={"Doctor" , "Patient" , "Nurse" , "Admin"},
     *       operationId = "refresh",
     *       summary = "refresh user's token",
     *       description= "Refresh Endpoint.",
     *       @OA\Response(response="200", description="OK"),
     *  )
     */
    public function refresh() {
        # When access token will be expired, we are going to generate a new one wit this function 
        # and return it here in response

         /** @var Illuminate\Auth\AuthManager */
         $auth = auth();
         return $this->respondWithToken($auth->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)   {
        # This function is used to make JSON response with new
        # access token of current user

         /** @var Illuminate\Auth\AuthManager */
         $auth = auth();

         return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $auth->factory()->getTTL() * 1
         ]);
    }


}


