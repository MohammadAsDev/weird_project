<?php

namespace App\Enums;

/**
 * @OA\Schema(
 *  type="integer",
 *  title="Medical Specialization Enum"
 * )
 */
enum MedicalSpecialization : int {
    case ANESTHESIOLOGY     = 0;
    case DERMATOLOGY        = 1;
    case INTERNAL           = 2;  
    case NEUROLOGY          = 3;
    case SURGERY            = 4;
}