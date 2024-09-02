<?php

namespace App\Enums;

enum Role : int {
    case ADMIN   = 0;
    case STAFF   = 1;
    case DOCTOR  = 2;
    case NURSE   = 3;
    case PATIENT = 4;
}
