<?php

namespace App\Enum;

enum StatutAbonnement: string
{
    case Actif = 'actif';
    case Inactif = 'inactif';
    case Suspendu = 'suspendu';
    case Expire = 'expire';
}
