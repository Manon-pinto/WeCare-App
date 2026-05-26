<?php

namespace App\Enum;

enum GraviteIncident: string
{
    case Faible = 'faible';
    case Moderee = 'moderee';
    case Grave = 'grave';
    case Critique = 'critique';
}
