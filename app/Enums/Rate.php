<?php

namespace App\Enums;

/**
 * @OA\Schema(
 *  type="integer",
 *  title="Rate Enum"
 * )
 */
enum Rate : int {
    case TRAINEE    = 0;
    case JUNIOR     = 1;
    case MED        = 2;
    case GOOD       = 3;
    case SENIOR     = 4;
}
