<?php

namespace App\Enum;

enum NiveauAcces: string
{
    case Lecture = 'lecture';
    case Standard = 'standard';
    case SuperAdmin = 'super_admin';
}
