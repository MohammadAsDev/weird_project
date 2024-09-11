<?php 

namespace App\Enums;


/**
 * @OA\Schema(
 *  type="integer",
 *  title="Appointement Status Enum",
 * )
 */
enum AppointementStatus : int {
    case NEED_ACK           = 0;
    case ACCEPTED           = 1;
    case REJECTED           = 2;
    case DELAYED            = 3;
};