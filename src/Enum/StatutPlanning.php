<?php

namespace App\Enum;

enum StatutPlanning: string
{
    case Brouillon = 'brouillon';
    case Publie = 'publie';
    case Archive = 'archive';
}
