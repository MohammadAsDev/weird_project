<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginForm;
use App\Models\User;
use Dotenv\Validator;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class AuthController extends Controller {

    public function __construct() {
        # By default we are using here auth:api middleware
        $this->middleware('auth:api', ['except' => ['login' , 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginForm $request) {
        $validated = $request->validated();
        if (! $token = auth()->attempt($validated)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token); # If all credentials are correct - we are going to generate a new access token and send it back on response
   }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me() {
        # Here we just get information about current user
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout(); # This is just logout function that will destroy access token of current user
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function confirm() {
        
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
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


