<?php 

namespace App\Enums;

enum BloodType : int {
    case APostive   = 0;
    case ANegative  = 1;
    case BPositive  = 2;
    case BNegatvie  = 3;
    case ABPositive = 4;
    case ABNegative = 5;
    case OPositive  = 6;
    case ONegative  = 7;
};