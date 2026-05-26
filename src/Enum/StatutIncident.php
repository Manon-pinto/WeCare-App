<?php

namespace App\Enum;

enum StatutIncident: string
{
    case Signale = 'signale';
    case EnTraitement = 'en_traitement';
    case Resolu = 'resolu';
    case Cloture = 'cloture';
}
