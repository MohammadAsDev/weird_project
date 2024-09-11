<?php

namespace App\Enums;


/**
 * @OA\Schema(
 *  type="integer",
 *  title="Gender Enum"
 * )
 */
enum Gender : int {
    case MALE   = 0;
    case FEMALE = 1;
}