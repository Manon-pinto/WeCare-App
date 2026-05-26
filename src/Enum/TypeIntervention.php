<?php

namespace App\Enum;

enum TypeIntervention: string
{
    case Menage = 'menage';
    case Toilette = 'toilette';
    case Repas = 'repas';
    case Accompagnement = 'accompagnement';
    case Surveillance = 'surveillance';
    case Soins = 'soins';
}
