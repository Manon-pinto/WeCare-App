<?php

namespace App\Enum;

enum TypeNotification: string
{
    case CompteRenduSoumis = 'compte_rendu_soumis';
    case CompteRenduValide = 'compte_rendu_valide';
    case IncidentSignale = 'incident_signale';
    case Rappel = 'rappel';
}
