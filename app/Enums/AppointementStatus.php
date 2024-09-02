<?php 

namespace App\Enums;

enum AppointementStatus : int {
    case COMPLETED      = 0;
    case CANCELED       = 1;
    case DELAYED        = 2;
    case WAITED         = 3;
};