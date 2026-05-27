<?php

namespace App\Enum;

enum StatutEntreprise: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspendue = 'suspendue';
}
