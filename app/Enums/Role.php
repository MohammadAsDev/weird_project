<?php

namespace App\Enums;

/**
 * @OA\Schema(
 *  type="integer",
 *  title="Role Enum"
 * )
 */
enum Role : int {
    case ADMIN      = 0;
    case STAFF      = 1;
    case DOCTOR     = 2;
    case NURSE      = 3;
    case PATIENT    = 4;
    case ANONYMOUS  = 5;
}
