<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Tymon\JWTAuth\Providers\Auth\Illuminate;

/**

    @OA\Info(
    version="1.0",
    title="Hospital Management API",
    description="API for hospital management system",
    @OA\Contact(name="Swagger API Team")
    )
    
    @OA\SecurityScheme(
    type="http",
    securityScheme="bearerAuth",
    scheme="bearer",
    bearerFormat="JWT"
    )

*/
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private const STRUCTURED_KEY = "structured";

    /**
     * Format model's data depending on a specific format
     * 
     * @param Illuminate\Database\Eloquent\Model model
     * @param format
     * @param parameter_list
     * @return response_data
    */
    public static function formatData(Model $model, $format , $parameter_list=[]) {
        $response_data = [];
        $attr = $model;
        foreach ($parameter_list as $param) {
            $attr = $attr->$param;
        } 

        if ($attr == null) {
            return null;
        }

        foreach($format as $key => $val) {
            if(is_array($val)) {
                array_push($parameter_list , $key);
                $sub_response_data = Controller::formatData($model , $val , $parameter_list);
                array_pop($parameter_list);
                if (
                    array_key_exists(Controller::STRUCTURED_KEY , $val)
                    && 
                    $val[Controller::STRUCTURED_KEY]
                ){
                    $response_data[$key] = $sub_response_data;    
                } else {
                    $response_data = array_merge($response_data , $sub_response_data);
                }
                continue;

            }
            if (!is_null($attr->$val)) {
                $response_data[$key] = $attr->$val;
            }
        }
        
        return $response_data;
    }

    /**
     * Format collection depending on a specific format
     * 
     * @param Illuminate\Database\Eloquent\Collection collection
     * @param format
     * @return response_data
     * 
    */
    public static function formatCollection(Collection $collection, $format) {
        $response_data = [];
        foreach ($collection as $item) {
            array_push(
                $response_data,
                Controller::formatData($item , $format)
            );
        }
        return $response_data;
    }
}
