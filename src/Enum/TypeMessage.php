<?php

namespace App\Enum;

enum TypeMessage: string
{
    case Direct = 'direct';
    case Systeme = 'systeme';
    case Alerte = 'alerte';
}
