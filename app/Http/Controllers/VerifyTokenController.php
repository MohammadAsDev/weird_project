<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchTokenForm;
use App\Models\User;
use App\Models\VerifyToken;

class VerifyTokenController extends Controller
{

    /**
     *  @OA\Post(
     *      path="/api/verify_tokens/search/",
     *      tags={"Admin"},
     *      operationId = "searchVerifyTokens",
     *      summary = "search on patient's verify token",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={"ssn"},
     *              @OA\Property(property="ssn",type="string"),
     *          ),
     *     ),
     *      description= "Search on Verify Tokens Endpoint.",
     *      @OA\Response(response="200", description="OK"),
     *      @OA\Response(response="403", description="Forbidden"),
     *      @OA\Response(response="422", description="Unprocessable Content"),
     *  )
     */
    protected function search(SearchTokenForm $request) {
        $this->authorize("viewAny" , VerifyToken::class);
        $validated = $request->validated();
        $user = User::where("ssn" , $validated["ssn"])->first();
        if ( $user == null ) {
            return response()->json([
                "details" => "no user has the same ssn"
            ] , 404);
        }

        $user_token =  $user->verifyToken->token;
        return response()->json([
            "verify_token" => $user_token
        ], 200);
    }
}
